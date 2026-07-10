<?php

namespace App\Domain\Inventories\DTOs;

final readonly class UpdateInventoryDTO
{
    public function __construct(
        public ?int $branchId = null,
        public ?int $itemId = null,
        public ?int $quantity = null,
        public ?string $expiryDate = null
    ) {}
}
