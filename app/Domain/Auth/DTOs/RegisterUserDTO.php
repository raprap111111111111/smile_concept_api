<?php

namespace App\Domain\Auth\DTOs;

final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null
    ) {}
}
