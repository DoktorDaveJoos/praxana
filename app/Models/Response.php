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
 * @property string $id
 * @property string $survey_run_id
 * @property string|null $step_id
 * @property string|null $choice_id
 * @property string $value
 * @property QuestionType $type
 * @property-read Choice|null $choice
 * @property-read Step|null $step
 * @property-read SurveyRun|null $surveyRun
 *
 * @method static ResponseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Response newModelQuery()
 * @method static Builder<static>|Response newQuery()
 * @method static Builder<static>|Response query()
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Response whereChoiceId($value)
 * @method static Builder<static>|Response whereCreatedAt($value)
 * @method static Builder<static>|Response whereId($value)
 * @method static Builder<static>|Response whereStepId($value)
 * @method static Builder<static>|Response whereSurveyRunId($value)
 * @method static Builder<static>|Response whereType($value)
 * @method static Builder<static>|Response whereUpdatedAt($value)
 * @method static Builder<static>|Response whereValue($value)
 *
 * @property int $is_skipped
 *
 * @method static Builder<static>|Response whereIsSkipped($value)
 *
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

    /**
     * Always return a human-friendly answer string.
     * Only single_choice and multiple_choice need special handling.
     */
    public function answerText(): string
    {
        $type = $this->step?->question_type?->value;

        // For everything else we expect plain strings already:
        if ($type !== 'single_choice' && $type !== 'multiple_choice') {
            return is_scalar($this->value) ? trim((string) $this->value) : $this->stringify($this->value);
        }

        // Value might be: id, JSON string, array object, or array of ids/objects
        $raw = $this->normalizeValue($this->value);

        return $type === 'single_choice'
            ? $this->singleChoiceText($raw)
            : $this->multipleChoiceText($raw);
    }

    /** Normalize to PHP types (decode JSON strings when needed). */
    protected function normalizeValue($v)
    {
        if (is_string($v)) {
            $s = ltrim($v);
            if ($s !== '' && in_array($s[0] ?? '', ['[', '{'], true)) {
                $decoded = json_decode($v, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
        }

        return $v;
    }

    /** Single choice: prefer related choice → object label/value → id lookup → fallback. */
    protected function singleChoiceText($raw): string
    {
        if ($this->choice) {
            return $this->choice->label ?? $this->choice->value ?? (string) $this->choice->id;
        }

        // If value is an object/array like {id, label, value}
        if (is_array($raw)) {
            if (array_key_exists('label', $raw) && $raw['label'] !== null) {
                return (string) $raw['label'];
            }
            if (array_key_exists('value', $raw) && $raw['value'] !== null) {
                return (string) $raw['value'];
            }
            if (array_key_exists('id', $raw) && $raw['id'] !== null) {
                return $this->choiceLabelById($raw['id']) ?? (string) $raw['id'];
            }

            return $this->stringify($raw);
        }

        // If it's a scalar id
        if (is_scalar($raw)) {
            return $this->choiceLabelById($raw) ?? trim((string) $raw);
        }

        return $this->stringify($raw);
    }

    /** Multiple choice: handle array of ids OR array of objects. */
    protected function multipleChoiceText($raw): string
    {
        $labels = [];

        if (is_array($raw)) {
            foreach ($raw as $item) {
                if (is_array($item)) {
                    $labels[] = $item['label'] ?? $item['value'] ?? (
                        isset($item['id']) ? ($this->choiceLabelById($item['id']) ?? (string) $item['id']) : $this->stringify($item)
                    );
                } else {
                    // scalar id
                    $labels[] = $this->choiceLabelById($item) ?? trim((string) $item);
                }
            }
        } else {
            // sometimes value might be a scalar or stringified list
            return $this->stringify($raw);
        }

        // Clean + join
        $labels = array_values(array_filter(array_map(fn ($x) => trim((string) $x), $labels)));

        return implode(', ', array_unique($labels));
    }

    protected function choiceLabelById($id): ?string
    {
        $choice = $this->step && method_exists($this->step, 'choices')
            ? $this->step->choices->firstWhere('id', (int) $id)
            : null;

        return $choice?->label ?? $choice?->value ?? null;
    }

    protected function stringify($v): string
    {
        if (is_null($v)) {
            return '';
        }
        if (is_scalar($v)) {
            return trim((string) $v);
        }

        return trim(json_encode($v, JSON_UNESCAPED_UNICODE));
    }
}
