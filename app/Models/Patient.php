<?php

namespace App\Models;

use App\HasHash;
use Database\Factories\PatientFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $practice_hash
 * @property string $first_name
 * @property string $last_name
 * @property Carbon|null $birth_date
 * @property string|null $gender
 * @property string|null $address
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $occupation
 * @property string|null $insurance_type
 * @property string|null $insurance_name
 * @property string|null $insurance_number
 * @property string|null $emergency_contact
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static PatientFactory factory($count = null, $state = [])
 * @method static Builder<static>|Patient newModelQuery()
 * @method static Builder<static>|Patient newQuery()
 * @method static Builder<static>|Patient onlyTrashed()
 * @method static Builder<static>|Patient query()
 * @method static Builder<static>|Patient whereAddress($value)
 * @method static Builder<static>|Patient whereBirthDate($value)
 * @method static Builder<static>|Patient whereCity($value)
 * @method static Builder<static>|Patient whereCountry($value)
 * @method static Builder<static>|Patient whereCreatedAt($value)
 * @method static Builder<static>|Patient whereDeletedAt($value)
 * @method static Builder<static>|Patient whereEmail($value)
 * @method static Builder<static>|Patient whereEmergencyContact($value)
 * @method static Builder<static>|Patient whereFirstName($value)
 * @method static Builder<static>|Patient whereGender($value)
 * @method static Builder<static>|Patient whereId($value)
 * @method static Builder<static>|Patient whereInsuranceName($value)
 * @method static Builder<static>|Patient whereInsuranceNumber($value)
 * @method static Builder<static>|Patient whereInsuranceType($value)
 * @method static Builder<static>|Patient whereLastName($value)
 * @method static Builder<static>|Patient whereOccupation($value)
 * @method static Builder<static>|Patient wherePhone($value)
 * @method static Builder<static>|Patient wherePostalCode($value)
 * @method static Builder<static>|Patient wherePracticeHash($value)
 * @method static Builder<static>|Patient whereUpdatedAt($value)
 * @method static Builder<static>|Patient withTrashed()
 * @method static Builder<static>|Patient withoutTrashed()
 *
 * @mixin Eloquent
 */
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, HasHash, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'practice_hash',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'address',
        'postal_code',
        'city',
        'country',
        'phone',
        'email',
        'occupation',
        'insurance_type',
        'insurance_name',
        'insurance_number',
        'emergency_contact',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'practice_hash',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
