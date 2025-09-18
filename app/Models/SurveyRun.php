<?php

namespace App\Models;

use App\SurveyRunStatus;
use Database\Factories\SurveyRunFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $survey_id
 * @property string $patient_hash
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property int|null $current_step_id
 * @property string $ai_analysis
 * @property SurveyRunStatus $status
 * @property-read Collection<int, Response> $responses
 * @property-read int|null $responses_count
 * @property-read Step|null $step
 * @property-read Survey|null $survey
 *
 * @method static SurveyRunFactory factory($count = null, $state = [])
 * @method static Builder<static>|SurveyRun newModelQuery()
 * @method static Builder<static>|SurveyRun newQuery()
 * @method static Builder<static>|SurveyRun query()
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|SurveyRun whereAiAnalysis($value)
 * @method static Builder<static>|SurveyRun whereCreatedAt($value)
 * @method static Builder<static>|SurveyRun whereCurrentStepId($value)
 * @method static Builder<static>|SurveyRun whereFinishedAt($value)
 * @method static Builder<static>|SurveyRun whereId($value)
 * @method static Builder<static>|SurveyRun wherePatientHash($value)
 * @method static Builder<static>|SurveyRun whereStartedAt($value)
 * @method static Builder<static>|SurveyRun whereStatus($value)
 * @method static Builder<static>|SurveyRun whereSurveyId($value)
 * @method static Builder<static>|SurveyRun whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class SurveyRun extends Model
{
    /** @use HasFactory<SurveyRunFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'survey_id',
        'patient_hash',
        'status',
        'started_at',
        'finished_at',
        'current_step_id',
        'ai_analysis',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'status' => SurveyRunStatus::class,
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function step(): HasOne
    {
        return $this->hasOne(Step::class, 'id', 'current_step_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Build the FULL PubMed prompt in one go.
     */
    public function pubmedPrompt(): string
    {
        $surveyName = $this->survey?->name ?? 'Survey';
        $surveyDesc = trim((string) $this->survey?->description);

        $prompt = "Based on the following patient survey responses, generate a focused PubMed search query that will find relevant medical literature:\n\n";
        $prompt .= "Survey: {$surveyName}\n";
        if ($surveyDesc !== '') {
            $prompt .= "Description: {$surveyDesc}\n";
        }
        $prompt .= "\nPatient Responses:\n";

        $i = 1;
        foreach ($this->responses as $response) {
            if (! $response->step) {
                continue;
            }

            // inside foreach ($this->responses as $response)
            $q = $response->step->title ?? 'Untitled question';
            $c = trim(($response->step->content ?? ''));
            $answer = $response->answerText(); // <<— fix: always a string now

            $prompt .= "{$i}. Question: {$q}\n";
            if ($c !== '') {
                $prompt .= "   Context: {$c}\n";
            }
            $prompt .= "   Answer: {$answer}\n\n";

            $prompt .= "\n";
            $i++;
        }

        $prompt .= "Please generate a PubMed search query that:\n";
        $prompt .= "1. Uses appropriate medical terminology and MeSH terms\n";
        $prompt .= "2. Combines relevant keywords with Boolean operators (AND, OR, NOT)\n";
        $prompt .= "3. Focuses on the most significant medical conditions or symptoms mentioned\n";
        $prompt .= "4. Is specific enough to return relevant results but not too narrow\n";
        $prompt .= "5. Follows PubMed search syntax best practices\n\n";
        $prompt .= 'Return only the search query without any additional explanation or formatting.';

        return $prompt;
    }
}
