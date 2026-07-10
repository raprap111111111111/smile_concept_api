<?php

namespace App\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class GetInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');
        return $invoice && $this->user()->can('view', $invoice);
    }

    public function rules(): array
    {
        return [];
    }
}
