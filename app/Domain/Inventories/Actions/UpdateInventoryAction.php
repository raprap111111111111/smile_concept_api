<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\DTOs\UpdateInventoryDTO;
use App\Domain\Inventories\Repositories\InventoryRepository;
use App\Domain\Inventories\Services\InventoryService;
use App\Models\Inventory;

class UpdateInventoryAction
{
    public function __construct(
        private readonly InventoryRepository $repository,
        private readonly InventoryService $service
    ) {}

    public function execute(Inventory $inventory, UpdateInventoryDTO $dto)
    {
        if ($dto->quantity !== null) {
            $this->service->validateQuantity($dto->quantity);
        }

        $branchId = $dto->branchId ?? $inventory->branch_id;
        $itemId = $dto->itemId ?? $inventory->item_id;

        if ($this->repository->hasExistingRecord($branchId, $itemId, $inventory->id)) {
            throw new \Exception("A duplicate inventory mapping conflicts with this action.");
        }

        $data = array_filter([
            'branch_id' => $dto->branchId,
            'item_id' => $dto->itemId,
            'quantity' => $dto->quantity,
            'expiry_date' => $dto->expiryDate,
        ], fn($value) => !is_null($value));

        return $this->repository->update($inventory, $data);
    }
}
