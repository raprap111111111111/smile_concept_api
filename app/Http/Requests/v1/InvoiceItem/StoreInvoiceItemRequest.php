<?php

namespace App\Http\Requests\v1\InvoiceItem;

use App\Models\InvoiceItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InvoiceItem::class);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'treatment_id' => ['required', 'integer', 'exists:treatments,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0.00'],
        ];
    }
}
