<?php

namespace App\Domain\Inventories\Mappers;

use App\Domain\Inventories\DTOs\CreateInventoryDTO;
use App\Domain\Inventories\DTOs\UpdateInventoryDTO;
use App\Http\Requests\v1\Inventory\StoreInventoryRequest;
use App\Http\Requests\v1\Inventory\UpdateInventoryRequest;

class InventoryMapper
{
    public static function fromCreateRequest(StoreInventoryRequest $request): CreateInventoryDTO
    {
        return new CreateInventoryDTO(
            branchId: (int) $request->validated('branch_id'),
            itemId: (int) $request->validated('item_id'),
            quantity: (int) $request->validated('quantity', 0),
            expiryDate: $request->validated('expiry_date')
        );
    }

    public static function fromUpdateRequest(UpdateInventoryRequest $request): UpdateInventoryDTO
    {
        return new UpdateInventoryDTO(
            branchId: $request->validated('branch_id') ? (int) $request->validated('branch_id') : null,
            itemId: $request->validated('item_id') ? (int) $request->validated('item_id') : null,
            quantity: $request->has('quantity') ? (int) $request->validated('quantity') : null,
            expiryDate: $request->validated('expiry_date')
        );
    }
}
