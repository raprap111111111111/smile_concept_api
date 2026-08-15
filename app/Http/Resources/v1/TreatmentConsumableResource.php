<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentConsumableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treatment_id' => $this->treatment_id,
            'item_id' => $this->item_id,
            'quantity_per_use' => $this->quantity_per_use,
            'is_optional' => $this->is_optional,
            'notes' => $this->notes,

            'item' => [
                'id' => $this->item?->id,
                'name' => $this->item?->name,
                'sku' => $this->item?->sku,
                'category' => $this->item?->category,
                'unit_of_measure' => $this->item?->unit_of_measure,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
