<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Str;

class UuidEloquentUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        if (! $this->isValidUuid($identifier)) {
            return null;
        }

        return parent::retrieveById($identifier);
    }

    /**
     * Retrieve a user by the given token and identifier.
     */
    public function retrieveByToken($identifier, $token)
    {
        if (! $this->isValidUuid($identifier)) {
            return null;
        }

        return parent::retrieveByToken($identifier, $token);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        // Delegate to parent; no UUID handling needed here
        parent::updateRememberToken($user, $token);
    }

    private function isValidUuid($value): bool
    {
        return is_string($value) && Str::isUuid($value);
    }
}


