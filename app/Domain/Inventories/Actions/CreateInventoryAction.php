<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\DTOs\CreateInventoryDTO;
use App\Domain\Inventories\Repositories\InventoryRepository;
use App\Domain\Inventories\Services\InventoryService;

class CreateInventoryAction
{
    public function __construct(
        private readonly InventoryRepository $repository,
        private readonly InventoryService $service
    ) {}

    public function execute(CreateInventoryDTO $dto)
    {
        $this->service->validateQuantity($dto->quantity);

        if ($this->repository->hasExistingRecord($dto->branchId, $dto->itemId)) {
            throw new \Exception("This item is already registered in this branch's inventory catalog. Update its quantities instead.");
        }

        return $this->repository->create([
            'branch_id' => $dto->branchId,
            'item_id' => $dto->itemId,
            'quantity' => $dto->quantity,
            'expiry_date' => $dto->expiryDate,
        ]);
    }
}
