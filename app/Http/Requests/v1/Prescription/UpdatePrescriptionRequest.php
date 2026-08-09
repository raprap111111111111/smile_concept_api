<?php

namespace App\Http\Requests\v1\Prescription;

use App\Models\Prescription;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Prescription $prescription */
        $prescription = $this->route('prescription');

        return $prescription
            && $this->user()->can('update', $prescription);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['sometimes', 'nullable', 'integer', 'exists:appointments,id'],
            'doctor_id'      => ['sometimes', 'nullable', 'integer', 'exists:doctors,id'],
            'user_id'        => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:2000'],

            // items (optional full replace / partial — adjust to your mapper)
            'items'                    => ['sometimes', 'array', 'min:1'],
            'items.*.id'               => ['sometimes', 'integer', 'exists:prescription_items,id'],
            'items.*.medicine_name'    => ['required_with:items', 'string', 'max:255'],
            'items.*.dosage'           => ['nullable', 'string', 'max:100'],
            'items.*.frequency'        => ['nullable', 'string', 'max:100'],
            'items.*.duration_days'    => ['nullable', 'integer', 'min:1'],
            'items.*.instructions'     => ['nullable', 'string', 'max:1000'],
        ];
    }
}