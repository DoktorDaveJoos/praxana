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
 *
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
}
