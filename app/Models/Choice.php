<?php

namespace App\Models;

use Database\Factories\ChoiceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $step_id
 * @property string $label
 * @property string $value
 * @property int $order
 * @property int|null $optional_next_step
 * @method static ChoiceFactory factory($count = null, $state = [])
 * @method static Builder<static>|Choice newModelQuery()
 * @method static Builder<static>|Choice newQuery()
 * @method static Builder<static>|Choice query()
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static>|Choice whereCreatedAt($value)
 * @method static Builder<static>|Choice whereId($value)
 * @method static Builder<static>|Choice whereLabel($value)
 * @method static Builder<static>|Choice whereOptionalNextStep($value)
 * @method static Builder<static>|Choice whereOrder($value)
 * @method static Builder<static>|Choice whereStepId($value)
 * @method static Builder<static>|Choice whereUpdatedAt($value)
 * @method static Builder<static>|Choice whereValue($value)
 * @property-read \App\Models\Step $step
 * @mixin Eloquent
 */
class Choice extends Model
{
    /** @use HasFactory<ChoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'step_id',
        'label',
        'value',
        'order',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }
}
