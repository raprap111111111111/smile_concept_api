<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'invoice_id'           => $this->invoice_id,
            'amount'               => $this->amount,
            'payment_method'       => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),

            // ✅ null-safe
            'payment_date'         => $this->payment_date?->toDateTimeString(),

            'transaction_reference' => $this->transaction_reference,
            'notes'                 => $this->notes,

            // ✅ whenLoaded avoids N+1 & null crashes
            'invoice' => $this->whenLoaded('invoice', fn() => [
                'id'             => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
                'total_amount'   => $this->invoice->total_amount,
                'balance_due'    => $this->invoice->balance_due,
                'status'         => $this->invoice->status?->value,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}