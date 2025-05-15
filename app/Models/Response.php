<?php

namespace App\Models;

use App\ResponseType;
use Database\Factories\ResponseFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $survey_run_id
 * @property string|null $step_id
 * @property string|null $choice_id
 * @property string $value
 *
 * @property ResponseType $type
 * @property-read Choice|null $choice
 * @property-read Step|null $step
 * @property-read SurveyRun|null $surveyRun
 *
 * @method static ResponseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Response newModelQuery()
 * @method static Builder<static>|Response newQuery()
 * @method static Builder<static>|Response query()
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
        'type' => ResponseType::class,
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
