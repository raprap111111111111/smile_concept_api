<?php

namespace App\Domain\Permission\DTOs;

final readonly class CreatePermissionDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}
}