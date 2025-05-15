<?php

namespace App\Models;

use App\ResponseType;
use Database\Factories\ResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property ResponseType $type
 * @property-read \App\Models\Choice|null $choice
 * @property-read \App\Models\Step|null $step
 * @property-read \App\Models\SurveyRun|null $surveyRun
 * @method static \Database\Factories\ResponseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Response newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Response newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Response query()
 * @mixin \Eloquent
 */
class Response extends Model
{
    /** @use HasFactory<ResponseFactory> */
    use HasFactory;

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
