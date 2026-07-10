<?php

namespace App\Domain\RecallTypes\DTOs;

final readonly class UpdateRecallTypeDTO
{
    public function __construct(
        public ?string $slug = null,
        public ?string $label = null,
        public ?int $frequencyMonths = null,
        public ?bool $isActive = null
    ) {}
}
