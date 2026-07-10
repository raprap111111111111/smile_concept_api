<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,   // ✅ added
            'appointment_id' => $this->appointment_id,
            'total_amount'   => $this->total_amount,
            'balance_due'    => $this->balance_due,
            'due_date'       => $this->due_date,          // ✅ added
            'notes'          => $this->notes,             // ✅ added

            'status'         => $this->status?->value,
            'status_label'   => $this->status?->label(),
            'status_color'   => $this->status?->color(),

            // ✅ whenLoaded prevents crash when relations are not eager loaded
            'items'    => $this->whenLoaded('items', fn() =>
                $this->items->map(fn($item) => [
                    'id'             => $item->id,
                    'treatment_id'   => $item->treatment_id,
                    'treatment_name' => $item->treatment?->name,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'discount'       => $item->discount,
                    'total_price'    => $item->total_price,
                ])
            ),

            'payments' => $this->whenLoaded('payments', fn() =>
                $this->payments->map(fn($payment) => [
                    'id'                    => $payment->id,
                    'amount'                => $payment->amount,
                    'payment_method'        => $payment->payment_method,
                    'payment_date'          => $payment->payment_date,
                    'transaction_reference' => $payment->transaction_reference,
                    'notes'                 => $payment->notes,
                ])
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}