<?php

namespace App\Domain\ToothConditions\DTOs;

final readonly class CreateToothConditionDTO
{
    public function __construct(
        public string $slug,
        public string $label,
        public string $colorCode,
        public bool $isActive,
    ) {}
}
