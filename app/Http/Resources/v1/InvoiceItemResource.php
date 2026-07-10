<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'treatment_id' => $this->treatment_id,
            'treatment_name' => $this->treatment?->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount' => $this->discount,
            'total_price' => $this->total_price,
            'invoice' => [
                'id' => $this->invoice?->id,
                'total_amount' => $this->invoice?->total_amount,
                'balance_due' => $this->invoice?->balance_due,
                'status' => $this->invoice?->status?->value,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
