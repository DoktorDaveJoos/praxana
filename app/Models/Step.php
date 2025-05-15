<?php

namespace App\Models;

use App\QuestionType;
use App\StepType;
use Database\Factories\StepFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $survey_id
 * @property string $title
 * @property string $content
 * @property array $options
 *
 * @property StepType $step_type
 * @property QuestionType $question_type
 * @property-read Collection<int, Choice> $choices
 * @property-read int|null $choices_count
 * @property-read Survey|null $survey
 *
 * @method static StepFactory factory($count = null, $state = [])
 * @method static Builder<static>|Step newModelQuery()
 * @method static Builder<static>|Step newQuery()
 * @method static Builder<static>|Step query()
 *
 * @mixin Eloquent
 */
class Step extends Model
{
    /** @use HasFactory<StepFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'title',
        'content',
        'step_type',
        'question_type',
        'options',
        'default_next_step_id',
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
}
