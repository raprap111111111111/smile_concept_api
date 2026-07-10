<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\Repositories\InventoryRepository;
use App\Models\Inventory;

class DeleteInventoryAction
{
    public function __construct(
        private readonly InventoryRepository $repository
    ) {}

    public function execute(Inventory $inventory): bool
    {
        return $this->repository->delete($inventory);
    }
}
