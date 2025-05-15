<?php

namespace App\Models;

use Database\Factories\ChoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @method static \Database\Factories\ChoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Choice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Choice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Choice query()
 * @mixin \Eloquent
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
