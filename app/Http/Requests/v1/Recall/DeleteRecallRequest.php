<?php

namespace App\Http\Requests\v1\Recall;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRecallRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recall = $this->route('recall');
        return $recall && $this->user()->can('delete', $recall);
    }

    public function rules(): array
    {
        return [];
    }
}
