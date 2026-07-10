<?php

namespace App\Domain\Role\DTOs;

final readonly class CreateRoleDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}
}