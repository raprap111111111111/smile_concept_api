<?php

namespace App\Http\Requests\v1\Notification;

use Illuminate\Foundation\Http\FormRequest;

class GetAllNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Users can always view their own notifications
    }

    public function rules(): array
    {
        return [
            'unread_only' => ['nullable', 'boolean'],
            'type'        => ['nullable', 'string', 'max:255'],
            'offset'      => ['nullable', 'integer', 'min:0'],
            'limit'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}