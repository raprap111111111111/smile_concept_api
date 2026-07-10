<?php

namespace App\Http\Requests\v1\Payment;

use Illuminate\Foundation\Http\FormRequest;

class DeletePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        return $payment && $this->user()->can('delete', $payment);
    }

    public function rules(): array
    {
        return [];
    }
}
