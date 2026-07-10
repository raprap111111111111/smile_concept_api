<?php

namespace App\Domain\Items\Actions;

use App\Domain\Items\Repositories\ItemRepository;
use App\Models\Item;

class DeleteItemAction
{
    public function __construct(
        private readonly ItemRepository $repository
    ) {}

    public function execute(Item $item): bool
    {
        // Safe delete will block if item exists in active transactions
        return $this->repository->delete($item);
    }
}
