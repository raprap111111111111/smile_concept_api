<?php

namespace App\Domain\User\DTOs;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?int $branchId = null,
        public string $password,
        public bool $isActive = true,
    ) {}
}