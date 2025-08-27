<?php

namespace App\Jobs;

use App\Models\SurveyRun;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ProcessSurveyCompletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public SurveyRun $surveyRun)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Log::info("Processing survey completion", ['survey_run_id' => $this->surveyRun->getKey()]);

            // Load the minimum we need, only if not already loaded by SerializesModels
            $this->surveyRun->loadMissing(['survey', 'responses.step', 'responses.choice']);

            // ONE function builds the entire PubMed prompt
            $prompt = $this->surveyRun->pubmedPrompt();

            // Get PubMed search query from LLM
            $searchQuery = $this->generatePubMedSearchQuery($prompt);
            if (!strlen($searchQuery)) {
                throw new Exception('Failed to generate a PubMed query from LLM.');
            }

            // Hit PubMed (search and summaries)
            $pubmed = $this->pubMedSearchWithSummaries($searchQuery);

            // Summarize & synthesize evidence into Markdown with citations
            $summaryMarkdown = $this->generateSummary($prompt, $pubmed);

            // Store all (array cast on a model handles JSON column)
            $this->surveyRun->update([
                'ai_analysis' => [
                    'prompt' => $prompt,
                    'search_query' => $searchQuery,
                    'pubmed_results' => $pubmed,
                    'processed_at' => now()->toISOString(),
                    'summary' => $summaryMarkdown,
                ],
            ]);

            Log::info("Survey completion processed", ['survey_run_id' => $this->surveyRun->getKey()]);
        } catch (Exception $e) {
            Log::error("Processing survey completion failed", [
                'survey_run_id' => $this->surveyRun->getKey(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a concise, structured markdown summary with inline PubMed PMIDs as citations.
     */
    private function generateSummary(string $prompt, array $pubmed): string
    {
        // Prepare a compact evidence bundle for the LLM
        $articles = Arr::get($pubmed, 'articles', []);
        $count = (int)Arr::get($pubmed, 'count', 0);

        // If nothing came back, return a graceful note so UI can still render
        if (empty($articles)) {
            $query = $pubmed['query'] ?? '';

            return <<<MD
                    ### Evidence Summary

                    No relevant PubMed records were retrieved for this case at the moment.
                    **Query used:** `{$query}`
                    **Total results reported by PubMed:** {$count}

                    Consider broadening the search terms or removing restrictive filters (e.g., age group, language, publication year).
                    MD;
        }

        $evidenceForLLM = $this->formatArticlesForLLM($articles);

        $system = <<<SYS
                    You are a clinically rigorous medical evidence synthesizer. Your job is to:
                    1) Read patient context and goals.
                    2) Review the PubMed records provided (title, authors, journal, pub date, PMID).
                    3) Produce a careful, **concise Markdown** synthesis with:
                       - "Key Takeaways" (3–7 bullets, patient-oriented, uncertainty-aware).
                       - "Evidence Map" as a table with columns: Study (link title to PubMed), Year, Design, N, Population, Key Finding, Limitations, PMID.
                       - "Clinical Considerations" (what to discuss with patient; monitoring; red flags).
                       - "Gaps & Limitations" (conflicts, sample sizes, bias, generalizability).
                    Citations: When referencing a finding, append **[PMID:#######]**. Prefer higher-quality evidence when ranking.
                    Be conservative; flag observational/low-quality findings appropriately. No hallucinations; only use provided records.
                    SYS;

        $user = <<<USR
                    ## Patient/Situation
                    {$prompt}

                    ## Retrieved PubMed Records ({$count} total; top shown below)
                    {$evidenceForLLM}
                    USR;

        try {
            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-5-mini')
                ->withSystemPrompt($system)
                ->withPrompt($user)
                ->asText();

            $text = trim($response->text ?? '');
            return $text !== '' ? $text : "_No summary generated._";
        } catch (Exception $e) {
            Log::error('LLM summary generation failed', ['error' => $e->getMessage()]);
            return "_Summary generation failed: {$e->getMessage()}_";
        }
    }

    private function generatePubMedSearchQuery(string $prompt): string
    {
        $sys = 'You are a medical research assistant specialized in creating effective PubMed search queries. '
            . 'Analyze patient survey responses and generate a precise PubMed search query. '
            . 'Prefer MeSH when obvious; include Boolean operators; avoid overly broad terms; no explanations, just the query.';

        try {
            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-5-mini')
                ->withSystemPrompt($sys)
                ->withPrompt($prompt)
                ->asText();

            return trim($response->text ?? '');
        } catch (Exception $e) {
            Log::error('LLM PubMed query generation failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Compact helper: esearch → esummary in one call-site.
     */
    private function pubMedSearchWithSummaries(string $query): array
    {
        $apiKey = config('services.ncbi.api_key'); // optional, increases rate limits
        $common = ['db' => 'pubmed', 'retmode' => 'json'];

        try {
            $search = Http::timeout(30)->retry(2, 500)
                ->get('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array_filter([
                    ...$common,
                    'term' => $query,
                    'retmax' => 20,
                    'sort' => 'relevance',
                    'api_key' => $apiKey,
                ]));

            if (!$search->successful()) {
                throw new Exception("esearch failed with status {$search->status()}");
            }

            $data = $search->json();
            $pmids = (array)data_get($data, 'esearchresult.idlist', []);
            $count = (int)data_get($data, 'esearchresult.count', 0);

            $articles = [];
            if ($pmids) {
                $summary = Http::timeout(30)->retry(2, 500)
                    ->get('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', array_filter([
                        ...$common,
                        'id' => implode(',', $pmids),
                        'api_key' => $apiKey,
                    ]));

                if ($summary->successful()) {
                    $sum = $summary->json();
                    foreach ($pmids as $pmid) {
                        $a = data_get($sum, "result.$pmid");
                        if (!$a) continue;

                        $authors = $this->formatAuthors((array)data_get($a, 'authors', []));
                        $journal = $a['fulljournalname'] ?? ($a['source'] ?? 'Unknown journal');
                        $title = trim($a['title'] ?? '') ?: 'No title available';
                        $pubdate = $a['pubdate'] ?? 'Unknown date';

                        // NLM 'elocationid' can contain DOI or page info; keep raw but also try to parse DOI if present
                        $elocation = $a['elocationid'] ?? null;
                        $doi = null;
                        if (is_string($elocation) && preg_match('/10\.\d{4,9}\/\S+/i', $elocation, $m)) {
                            $doi = $m[0];
                        }

                        $articles[] = [
                            'pmid' => (string)$pmid,
                            'title' => $title,
                            'authors' => $authors,
                            'journal' => $journal,
                            'pub_date' => $pubdate,
                            'doi' => $doi,
                            'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/",
                            'study' => $a['pubtype'] ?? null, // can help LLM with design
                        ];
                    }
                }
            }

            return [
                'query' => $query,
                'count' => $count,
                'pmids' => $pmids,
                'articles' => $articles,
            ];
        } catch (Exception $e) {
            Log::error('PubMed lookup failed', ['query' => $query, 'error' => $e->getMessage()]);
            return [
                'query' => $query,
                'count' => 0,
                'pmids' => [],
                'articles' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    private function formatAuthors(array $authors): string
    {
        if (!$authors) return 'No authors listed';
        $top = array_slice($authors, 0, 3);
        $names = array_map(fn($a) => $a['name'] ?? 'Unknown author', $top);
        return count($authors) > 3 ? implode(', ', $names) . ', et al.' : implode(', ', $names);
    }

    /**
     * Prepare a compact bullet list for the LLM (keeps tokens low but informative).
     */
    private function formatArticlesForLLM(array $articles): string
    {
        $lines = array_map(function (array $a) {
            $pmid = $a['pmid'] ?? '';
            $title = $a['title'] ?? 'No title';
            $auth = $a['authors'] ?? 'Unknown authors';
            $jour = $a['journal'] ?? 'Unknown journal';
            $date = $a['pub_date'] ?? 'Unknown date';
            $design = is_array($a['study'] ?? null) ? implode(', ', $a['study']) : ($a['study'] ?? 'n/a');
            $url = $a['url'] ?? ("https://pubmed.ncbi.nlm.nih.gov/{$pmid}/");

            return "- **{$title}** ({$jour}, {$date}) — {$auth}. Design: {$design}. PMID: {$pmid}. {$url}";
        }, $articles);

        return implode("\n", $lines);
    }
}
