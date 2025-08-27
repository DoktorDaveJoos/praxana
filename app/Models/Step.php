<?php

namespace App\Models;

use App\QuestionType;
use App\StepType;
use Database\Factories\StepFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property int $id
 * @property int $survey_id
 * @property int $order
 * @property string $title
 * @property string $content
 * @property array $options
 * @property StepType $step_type
 * @property QuestionType $question_type
 * @property-read Collection<int, Choice> $choices
 * @property-read int|null $choices_count
 * @property-read Survey|null $survey
 * @method static StepFactory factory($count = null, $state = [])
 * @method static Builder<static>|Step newModelQuery()
 * @method static Builder<static>|Step newQuery()
 * @method static Builder<static>|Step query()
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $next_step_id
 * @property-read string|null $previous_step_id
 * @method static Builder<static>|Step whereContent($value)
 * @method static Builder<static>|Step whereCreatedAt($value)
 * @method static Builder<static>|Step whereId($value)
 * @method static Builder<static>|Step whereOptions($value)
 * @method static Builder<static>|Step whereOrder($value)
 * @method static Builder<static>|Step whereQuestionType($value)
 * @method static Builder<static>|Step whereStepType($value)
 * @method static Builder<static>|Step whereSurveyId($value)
 * @method static Builder<static>|Step whereTitle($value)
 * @method static Builder<static>|Step whereUpdatedAt($value)
 * @property-read Collection<int, \App\Models\Response> $responses
 * @property-read int|null $responses_count
 * @mixin Eloquent
 */
class Step extends Model
{
    /** @use HasFactory<StepFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'survey_id',
        'order',
        'title',
        'content',
        'step_type',
        'question_type',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
        'step_type' => StepType::class,
        'question_type' => QuestionType::class,
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Returns the next step in the same survey by `order`, or null if last.
     */
    public function nextStep(): ?Step
    {
        return $this->survey()
            ->firstOrFail()             // ensure survey is loaded or 404 in contexts like API
            ->steps()
            ->where('order', '>', $this->order)
            ->orderBy('order', 'asc')
            ->first();
    }

    /**
     * Returns the previous step in the same survey by `order`, or null if first.
     */
    public function previousStep(): ?Step
    {
        return $this->survey()
            ->firstOrFail()
            ->steps()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Accessor: `$step->next_step_id`
     */
    public function getNextStepIdAttribute(): ?string
    {
        return $this->nextStep()?->id;
    }

    /**
     * Accessor: `$step->previous_step_id`
     */
    public function getPreviousStepIdAttribute(): ?string
    {
        return $this->previousStep()?->id;
    }
}
