<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * Generate a unique hash for the user.
     *
     * This method creates a secure hash using the patient's ID and the application key.
     * The hash is generated using the HMAC-SHA256 algorithm, ensuring uniqueness
     * and security for identifying the user in external systems or processes.
     *
     * @return string The generated user hash.
     */
    public function getPatientHash(): string
    {
        return hash_hmac('sha256', (string) $this->id, config('app.key'));
    }
}
