<?php

namespace App\Domain\Inventories\Repositories;

use App\Models\Inventory;
use App\Support\Query\BaseRepository;

class InventoryRepository extends BaseRepository
{
    protected string $model = Inventory::class;

    protected array $searchable = [];

    protected array $filterable = [
        'branch_id',
        'item_id',
    ];

    protected array $sortable = [
        'id',
        'quantity',
        'expiry_date',
        'created_at',
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    /**
     * Check if a specific item is already registered in a branch's inventory
     */
    public function hasExistingRecord(int $branchId, int $itemId, ?int $excludeId = null): bool
    {
        $query = ($this->model)::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
