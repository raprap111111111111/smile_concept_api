<?php

namespace App\Domain\ToothConditions\DTOs;

final readonly class UpdateToothConditionDTO
{
    public function __construct(
        public ?string $slug = null,
        public ?string $label = null,
        public ?string $colorCode = null,
        public ?bool $isActive = null,
    ) {}
}
