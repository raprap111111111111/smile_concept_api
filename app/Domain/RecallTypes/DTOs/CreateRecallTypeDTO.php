<?php

namespace App\Domain\RecallTypes\DTOs;

final readonly class CreateRecallTypeDTO
{
    public function __construct(
        public string $slug,
        public string $label,
        public int $frequencyMonths,
        public bool $isActive
    ) {}
}
