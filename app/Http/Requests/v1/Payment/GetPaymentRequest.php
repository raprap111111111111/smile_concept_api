<?php

namespace App\Http\Requests\v1\Payment;

use Illuminate\Foundation\Http\FormRequest;

class GetPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        return $payment && $this->user()->can('view', $payment);
    }

    public function rules(): array
    {
        return [];
    }
}
