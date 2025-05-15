<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $description
 * @property string $version
 * @property bool $is_active
 *
 * @property-read Collection<int, Step> $steps
 * @property-read int|null $steps_count
 * @property-read Collection<int, SurveyRun> $surveyRuns
 * @property-read int|null $survey_runs_count
 *
 * @method static SurveyFactory factory($count = null, $state = [])
 * @method static Builder<static>|Survey newModelQuery()
 * @method static Builder<static>|Survey newQuery()
 * @method static Builder<static>|Survey query()
 *
 * @mixin Eloquent
 */
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }

    public function surveyRuns(): HasMany
    {
        return $this->hasMany(SurveyRun::class);
    }
}
