<?php

namespace App\Http\Requests\v1\ClinicalNote;

use Illuminate\Foundation\Http\FormRequest;

class DeleteClinicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $note = $this->route('clinical_note');
        return $note && $this->user()->can('delete', $note);
    }

    public function rules(): array
    {
        return [];
    }
}
