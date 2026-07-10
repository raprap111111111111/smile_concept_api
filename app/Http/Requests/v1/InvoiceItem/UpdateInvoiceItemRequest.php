<?php

namespace App\Http\Requests\v1\InvoiceItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('invoice_item');
        return $item && $this->user()->can('update', $item);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['sometimes', 'required', 'integer', 'exists:invoices,id'],
            'treatment_id' => ['sometimes', 'required', 'integer', 'exists:treatments,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'discount' => ['sometimes', 'nullable', 'numeric', 'min:0.00'],
        ];
    }
}
