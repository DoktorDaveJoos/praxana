<?php

namespace App\Models;

use App\QuestionType;
use Database\Factories\ResponseFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string $id
 * @property string $survey_run_id
 * @property string|null $step_id
 * @property string|null $choice_id
 * @property string $value
 * @property QuestionType $type
 * @property-read Choice|null $choice
 * @property-read Step|null $step
 * @property-read SurveyRun|null $surveyRun
 * @method static ResponseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Response newModelQuery()
 * @method static Builder<static>|Response newQuery()
 * @method static Builder<static>|Response query()
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Response whereChoiceId($value)
 * @method static Builder<static>|Response whereCreatedAt($value)
 * @method static Builder<static>|Response whereId($value)
 * @method static Builder<static>|Response whereStepId($value)
 * @method static Builder<static>|Response whereSurveyRunId($value)
 * @method static Builder<static>|Response whereType($value)
 * @method static Builder<static>|Response whereUpdatedAt($value)
 * @method static Builder<static>|Response whereValue($value)
 * @property int $is_skipped
 * @method static Builder<static>|Response whereIsSkipped($value)
 * @mixin Eloquent
 */
class Response extends Model
{
    /** @use HasFactory<ResponseFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'survey_run_id',
        'step_id',
        'choice_id',
        'type',
        'value',
    ];

    protected $casts = [
        'type' => QuestionType::class,
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function surveyRun(): BelongsTo
    {
        return $this->belongsTo(SurveyRun::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }
}
