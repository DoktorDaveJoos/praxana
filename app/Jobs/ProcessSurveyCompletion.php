<?php

namespace App\Jobs;

use App\Models\SurveyRun;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Prism;

class ProcessSurveyCompletion implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SurveyRun $surveyRun
    )
    {
        //
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Log::info("Processing survey completion for SurveyRun ID: {$this->surveyRun->id}");

            // Load survey run with all related data
            $this->surveyRun->load([
                'survey.steps',
                'responses.step',
                'responses.choice'
            ]);

            // Build the prompt from questions and answers
            $surveyData = $this->buildSurveyDataPrompt();

            // Generate PubMed search query using AI
            $searchQuery = $this->generatePubMedSearchQuery($surveyData);

            // Perform PubMed search
            $pubmedResults = $this->searchPubMed($searchQuery);

            // Store the analysis results
            $this->surveyRun->update([
                'ai_analysis' => json_encode([
                    'survey_data' => $surveyData,
                    'search_query' => $searchQuery,
                    'pubmed_results' => $pubmedResults,
                    'processed_at' => now()->toISOString(),
                ])
            ]);

            Log::info("Successfully processed survey completion for SurveyRun ID: {$this->surveyRun->id}");

        } catch (Exception $e) {
            Log::error("Failed to process survey completion for SurveyRun ID: {$this->surveyRun->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Build a structured prompt from survey questions and answers
     */
    private function buildSurveyDataPrompt(): array
    {
        $surveyData = [
            'survey_name' => $this->surveyRun->survey->name,
            'survey_description' => $this->surveyRun->survey->description ?? '',
            'questions_and_answers' => []
        ];

        foreach ($this->surveyRun->responses as $response) {
            if (!$response->step) continue;

            $questionData = [
                'question' => $response->step->title,
                'content' => $response->step->content,
                'answer' => $response->value,
                'question_type' => $response->step->question_type?->value ?? 'unknown'
            ];

            // If there's a choice selected, include choice text
            if ($response->choice) {
                $questionData['choice_text'] = $response->choice->text ?? '';
            }

            $surveyData['questions_and_answers'][] = $questionData;
        }

        return $surveyData;
    }

    /**
     * Generate a PubMed search query using AI based on survey data
     */
    private function generatePubMedSearchQuery(array $surveyData): string
    {
        $prompt = $this->buildAIPrompt($surveyData);

        $response = Prism::text()
            ->using('openai', 'gpt-4')
            ->withSystemPrompt('You are a medical research assistant specialized in creating effective PubMed search queries. Your task is to analyze patient survey responses and generate precise, relevant PubMed search queries that will find the most pertinent medical literature.')
            ->withPrompt($prompt)
            ->asText();

        return trim($response->text);
    }

    /**
     * Build the AI prompt from survey data
     */
    private function buildAIPrompt(array $surveyData): string
    {
        $prompt = "Based on the following patient survey responses, generate a focused PubMed search query that will find relevant medical literature:\n\n";

        $prompt .= "Survey: {$surveyData['survey_name']}\n";
        if (!empty($surveyData['survey_description'])) {
            $prompt .= "Description: {$surveyData['survey_description']}\n";
        }
        $prompt .= "\nPatient Responses:\n";

        foreach ($surveyData['questions_and_answers'] as $index => $qa) {
            $prompt .= ($index + 1) . ". Question: {$qa['question']}\n";
            if (!empty($qa['content'])) {
                $prompt .= "   Context: {$qa['content']}\n";
            }
            $prompt .= "   Answer: {$qa['answer']}\n";
            if (!empty($qa['choice_text'])) {
                $prompt .= "   Selected: {$qa['choice_text']}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Please generate a PubMed search query that:\n";
        $prompt .= "1. Uses appropriate medical terminology and MeSH terms\n";
        $prompt .= "2. Combines relevant keywords with Boolean operators (AND, OR, NOT)\n";
        $prompt .= "3. Focuses on the most significant medical conditions or symptoms mentioned\n";
        $prompt .= "4. Is specific enough to return relevant results but not too narrow\n";
        $prompt .= "5. Follows PubMed search syntax best practices\n\n";
        $prompt .= "Return only the search query without any additional explanation or formatting.";

        return $prompt;
    }

    /**
     * Search PubMed using the generated query
     */
    private function searchPubMed(string $query): array
    {
        try {
            // PubMed E-utilities API endpoint
            $baseUrl = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi';

            $response = Http::get($baseUrl, [
                'db' => 'pubmed',
                'term' => $query,
                'retmode' => 'json',
                'retmax' => 20, // Limit to 20 results
                'sort' => 'relevance'
            ]);

            if (!$response->successful()) {
                throw new Exception("PubMed API request failed: " . $response->status());
            }

            $data = $response->json();

            if (!isset($data['esearchresult']['idlist'])) {
                return [
                    'query' => $query,
                    'count' => 0,
                    'articles' => [],
                    'error' => 'No results found or invalid response format'
                ];
            }

            $pmids = $data['esearchresult']['idlist'];
            $totalCount = $data['esearchresult']['count'] ?? 0;

            // Get article details if we have PMIDs
            $articles = [];
            if (!empty($pmids)) {
                $articles = $this->fetchArticleDetails($pmids);
            }

            return [
                'query' => $query,
                'count' => $totalCount,
                'articles' => $articles,
                'pmids' => $pmids
            ];

        } catch (Exception $e) {
            Log::error("PubMed search failed", [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return [
                'query' => $query,
                'count' => 0,
                'articles' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch detailed article information from PubMed
     */
    private function fetchArticleDetails(array $pmids): array
    {
        try {
            $baseUrl = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi';

            $response = Http::get($baseUrl, [
                'db' => 'pubmed',
                'id' => implode(',', $pmids),
                'retmode' => 'json'
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $articles = [];

            if (isset($data['result'])) {
                foreach ($pmids as $pmid) {
                    if (isset($data['result'][$pmid])) {
                        $article = $data['result'][$pmid];
                        $articles[] = [
                            'pmid' => $pmid,
                            'title' => $article['title'] ?? 'No title available',
                            'authors' => $this->formatAuthors($article['authors'] ?? []),
                            'journal' => $article['fulljournalname'] ?? $article['source'] ?? 'Unknown journal',
                            'pub_date' => $article['pubdate'] ?? 'Unknown date',
                            'doi' => $article['elocationid'] ?? null,
                            'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/"
                        ];
                    }
                }
            }

            return $articles;

        } catch (Exception $e) {
            Log::error("Failed to fetch article details", [
                'pmids' => $pmids,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Format author names from PubMed response
     */
    private function formatAuthors(array $authors): string
    {
        if (empty($authors)) {
            return 'No authors listed';
        }

        $authorNames = array_map(function ($author) {
            return $author['name'] ?? 'Unknown author';
        }, array_slice($authors, 0, 3)); // Limit to first 3 authors

        $result = implode(', ', $authorNames);

        if (count($authors) > 3) {
            $result .= ', et al.';
        }

        return $result;
    }
}
