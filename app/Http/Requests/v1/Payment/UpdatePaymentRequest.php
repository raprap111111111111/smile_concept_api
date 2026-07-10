<?php

namespace App\Http\Requests\v1\Payment;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        return $payment && $this->user()->can('update', $payment);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['sometimes', 'required', 'integer', 'exists:invoices,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'payment_method' => ['sometimes', 'required', 'string', Rule::enum(PaymentMethod::class)],
            'payment_date' => ['sometimes', 'required', 'date_format:Y-m-d H:i:s'],
            'transaction_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
