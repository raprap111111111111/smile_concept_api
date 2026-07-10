<?php

namespace App\Http\Requests\v1\InvoiceItem;

use Illuminate\Foundation\Http\FormRequest;

class GetInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('invoice_item');
        return $item && $this->user()->can('view', $item);
    }

    public function rules(): array
    {
        return [];
    }
}
