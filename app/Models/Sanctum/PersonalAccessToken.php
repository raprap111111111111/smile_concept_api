<?php

namespace App\Models\Sanctum;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Support\Str;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Override the boot method to force a 64-character token generation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            // Force the underlying random hash token to be 64 characters
            $token->token = hash('sha256', $plainTextToken = Str::random(64));
        });
    }
}