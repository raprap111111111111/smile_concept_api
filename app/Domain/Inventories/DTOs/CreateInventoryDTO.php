<?php

namespace App\Domain\Inventories\DTOs;

final readonly class CreateInventoryDTO
{
    public function __construct(
        public int $branchId,
        public int $itemId,
        public int $quantity = 0,
        public ?string $expiryDate = null
    ) {}
}
