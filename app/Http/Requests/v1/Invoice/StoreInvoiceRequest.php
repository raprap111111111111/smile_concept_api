<?php

namespace App\Http\Requests\v1\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        return [
            'appointment_id'           => ['required', 'integer', 'exists:appointments,id',
                                           'unique:invoices,appointment_id'], // ✅ one invoice per appointment

            'items'                    => ['required', 'array', 'min:1'],
            'items.*.treatment_id'     => ['required', 'integer', 'exists:treatments,id'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.discount'         => ['nullable', 'numeric', 'min:0'],

            'notes'                    => ['nullable', 'string', 'max:1000'],  // ✅ added
            'due_date'                 => ['nullable', 'date', 'after:today'], // ✅ added
        ];
    }
}