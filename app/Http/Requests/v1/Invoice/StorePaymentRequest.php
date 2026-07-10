<?php

namespace App\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');
        return $invoice && $this->user()->can('update', $invoice); // Must have update rights on parent invoice
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,card,bank_transfer,insurance'],
            'payment_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
