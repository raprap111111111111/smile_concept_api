<?php

namespace App\Http\Requests\v1\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class GetTreatmentConsumablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('treatment.view')
            || $this->user()->can('treatment.viewAny');
    }

    public function rules(): array
    {
        return [];
    }
}
