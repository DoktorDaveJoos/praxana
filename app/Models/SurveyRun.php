<?php

namespace App\Models;

use App\SurveyRunStatus;
use Database\Factories\SurveyRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property SurveyRunStatus $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Response> $responses
 * @property-read int|null $responses_count
 * @property-read \App\Models\Step|null $step
 * @property-read \App\Models\Survey|null $survey
 * @method static \Database\Factories\SurveyRunFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyRun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyRun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyRun query()
 * @mixin \Eloquent
 */
class SurveyRun extends Model
{
    /** @use HasFactory<SurveyRunFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'patient_hash',
        'status',
        'started_at',
        'finished_at',
        'current_step_id',
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
