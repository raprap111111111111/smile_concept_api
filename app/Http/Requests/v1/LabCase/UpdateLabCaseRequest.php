<?php

namespace App\Http\Requests\v1\LabCase;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $labCase = $this->route('lab_case');
        return $labCase && $this->user()->can('update', $labCase);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['sometimes', 'required', 'integer', 'exists:appointments,id'],
            'lab_name' => ['sometimes', 'required', 'string', 'max:255'],
            'work_type' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:sent,in_progress,received,fitted,rejected'],
            'sent_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date'],
            'received_date' => ['sometimes', 'nullable', 'date'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
