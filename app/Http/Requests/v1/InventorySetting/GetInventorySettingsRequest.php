<?php

namespace App\Http\Requests\v1\InventorySetting;

use Illuminate\Foundation\Http\FormRequest;

class GetInventorySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.view');
    }

    public function rules(): array
    {
        return [];
    }
}
