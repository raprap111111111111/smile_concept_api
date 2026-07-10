<?php

namespace App\Domain\User\Services;

use Illuminate\Support\Facades\Hash;

class UserService
{
    public function hashPassword(string $password): string
    {
        return Hash::make($password);
    }
}