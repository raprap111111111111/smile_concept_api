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
        $this->assertNotStocked($item);

        return $this->repository->delete($item);
    }

    /**
     * An item that still has stock rows cannot be deleted.
     *
     * The foreign key is ON DELETE RESTRICT, so the database would reject this
     * anyway — but with a raw SQL constraint error. Checking here turns that
     * into a 409 with a message naming how many branches are affected, which is
     * what the caller actually needs in order to act.
     *
     * Deliberately counts every stock row, not just non-empty ones: a
     * zero-quantity row is still a branch's registration of this item, and it is
     * what the constraint keys off.
     *
     * @throws \RuntimeException
     */
    private function assertNotStocked(Item $item): void
    {
        $branchCount = $item->inventories()->count();

        if ($branchCount === 0) {
            return;
        }

        throw new \RuntimeException(sprintf(
            'This item is still stocked at %d %s. Remove its stock records first, '
            . 'or leave the item in place so its history stays intact.',
            $branchCount,
            $branchCount === 1 ? 'branch' : 'branches',
        ));
    }
}
