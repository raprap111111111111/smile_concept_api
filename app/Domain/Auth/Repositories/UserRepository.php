<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}