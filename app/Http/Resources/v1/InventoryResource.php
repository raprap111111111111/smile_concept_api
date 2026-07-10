<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'item_id' => $this->item_id,
            'quantity' => $this->quantity,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_low_stock' => $this->isLowStock(),
            'is_expired' => $this->isExpired(),
            
            // Nested Item Details
            'item' => [
                'id' => $this->item?->id,
                'name' => $this->item?->name,
                'sku' => $this->item?->sku,
                'category' => $this->item?->category,
                'unit_of_measure' => $this->item?->unit_of_measure,
                'minimum_threshold' => $this->item?->minimum_threshold,
            ],

            // Nested Branch Details
            'branch' => [
                'id' => $this->branch?->id,
                'name' => $this->branch?->name,
                'branch_code' => $this->branch?->branch_code,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
