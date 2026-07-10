<?php

namespace App\Domain\Inventories\Services;

class InventoryService
{
    /**
     * Ensure inventory count changes are structurally valid
     */
    public function validateQuantity(int $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Physical inventory quantities cannot be negative values.");
        }
    }
}
