<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}