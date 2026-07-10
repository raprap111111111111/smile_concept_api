<?php

namespace App\Domain\Role\DTOs;

final readonly class UpdateRoleDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?bool $isActive = null,
    ) {}
}