<?php

namespace App\Http\Requests\v1\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;

class GetActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity_logs.view');
    }

    public function rules(): array
    {
        return [];
    }
}