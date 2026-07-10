<?php

namespace App\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ["required", "email", "max:100"],
            'password' => ["required", "string", "min:6"],
        ];
    }

    protected function prepareForValidation()
    {
        $this->ensureIsNotRateLimited();
    }

    public function ensureIsNotRateLimited(): void
    {
        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    public function throttleKey(): string
    {
        $allInputs = $this->all();
        $email = isset($allInputs['email']) ? strtolower((string)$allInputs['email']) : '';
        return $email . '|' . $this->ip();
    }
}