<?php

namespace App\Domain\TreatmentConsumables\DTOs;

/** One line of a treatment's recipe. */
final readonly class TreatmentConsumableDTO
{
    public function __construct(
        public int $itemId,
        public int $quantityPerUse,
        public bool $isOptional = false,
        public ?string $notes = null,
    ) {}
}
