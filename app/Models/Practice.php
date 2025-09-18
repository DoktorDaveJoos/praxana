<?php

namespace App\Models;

use App\HasHash;
use Database\Factories\PracticeFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string|null $address
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\PracticeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Practice newModelQuery()
 * @method static Builder<static>|Practice newQuery()
 * @method static Builder<static>|Practice query()
 * @method static Builder<static>|Practice whereAddress($value)
 * @method static Builder<static>|Practice whereCity($value)
 * @method static Builder<static>|Practice whereCountry($value)
 * @method static Builder<static>|Practice whereCreatedAt($value)
 * @method static Builder<static>|Practice whereEmail($value)
 * @method static Builder<static>|Practice whereId($value)
 * @method static Builder<static>|Practice whereName($value)
 * @method static Builder<static>|Practice wherePhone($value)
 * @method static Builder<static>|Practice wherePostalCode($value)
 * @method static Builder<static>|Practice whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Practice extends Model
{
    /** @use HasFactory<PracticeFactory> */
    use HasFactory, HasHash, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'postal_code',
        'city',
        'country',
        'phone',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
