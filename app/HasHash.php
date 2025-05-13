<?php

namespace App;

/**
 * @property int|string $id
 */
trait HasHash
{
    /**
     * Generate a unique hash for the model.
     *
     * This method creates a secure hash using the model's ID and the application key.
     * The hash is generated using the HMAC-SHA256 algorithm, ensuring uniqueness
     * and security for identifying the user in external systems or processes.
     *
     * @return string The generated hash.
     */
    public function getHash(): string
    {
        return hash_hmac('sha256', (string) $this->id, config('app.key'));
    }
}
