<?php

namespace App\Domain\Auth\DTOs;

use App\Http\Requests\V1\Auth\LoginRequest;

readonly class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

}