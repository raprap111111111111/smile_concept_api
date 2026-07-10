<?php

namespace App\Domain\Permission\DTOs;

final readonly class UpdatePermissionDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?bool $isActive = null,
    ) {}
}