<?php

namespace App\Http\Requests\v1\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class GetPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prescription = $this->route('prescription');
        return $prescription && $this->user()->can('view', $prescription);
    }

    public function rules(): array
    {
        return [];
    }
}
