<?php

namespace App\Domain\User\DTOs;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public string $password,
        public ?string $phone = null,
        public ?int $branchId = null,
        public bool $isActive = true,
    ) {}
}