<?php

namespace App\Http\Requests\v1\LabCase;

use App\Models\LabCase;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LabCase::class);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'lab_name' => ['required', 'string', 'max:255'],
            'work_type' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:sent,in_progress,received,fitted,rejected'],
            'sent_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
