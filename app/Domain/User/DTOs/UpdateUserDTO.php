<?php

namespace App\Domain\User\DTOs;

final readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?array $branchIds = null,
        public ?string $password = null,
        public ?bool $isActive = null,
    ) {}
}