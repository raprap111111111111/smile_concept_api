<?php

namespace App\Domain\Inventories\DTOs;

final readonly class UpdateInventoryDTO
{
    public function __construct(
        public ?int $branchId = null,
        public ?int $itemId = null,
        public ?int $quantity = null,
        public ?string $expiryDate = null,
        /**
         * True when the request carried an `expiry_date` key at all, including an
         * explicit null. Without it, "clear the expiry" and "leave the expiry
         * alone" produce an identical DTO and the null is filtered away, so an
         * expiry date could never be removed once set.
         */
        public bool $expiryDateProvided = false,
    ) {}
}
