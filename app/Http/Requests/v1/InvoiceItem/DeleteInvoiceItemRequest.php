<?php

namespace App\Http\Requests\v1\InvoiceItem;

use Illuminate\Foundation\Http\FormRequest;

class DeleteInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('invoice_item');
        return $item && $this->user()->can('delete', $item);
    }

    public function rules(): array
    {
        return [];
    }
}
