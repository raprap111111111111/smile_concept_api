<?php

namespace App\Http\Requests\v1\Consent;

use Illuminate\Foundation\Http\FormRequest;

class VoidConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('consent-form.void');
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A void reason is required.',
            'reason.min'      => 'Reason must be at least 5 characters.',
        ];
    }
}