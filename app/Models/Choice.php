<?php

namespace App\Models;

use Database\Factories\ChoiceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $step_id
 * @property string $label
 * @property string $value
 * @property int $order
 * @property int|null $optional_next_step
 *
 * @method static ChoiceFactory factory($count = null, $state = [])
 * @method static Builder<static>|Choice newModelQuery()
 * @method static Builder<static>|Choice newQuery()
 * @method static Builder<static>|Choice query()
 *
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
}
