<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @propery int $id
 *
 * @property string $name
 * @property string $description
 * @property string $version
 * @property bool $is_active
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
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Survey whereCreatedAt($value)
 * @method static Builder<static>|Survey whereDescription($value)
 * @method static Builder<static>|Survey whereId($value)
 * @method static Builder<static>|Survey whereIsActive($value)
 * @method static Builder<static>|Survey whereName($value)
 * @method static Builder<static>|Survey whereUpdatedAt($value)
 * @method static Builder<static>|Survey whereVersion($value)
 *
 * @mixin Eloquent
 */
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'version',
        'is_active',
        'practice_hash',
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
