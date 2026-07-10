<?php

namespace App\Domain\Auth\Mappers;

use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Http\Requests\v1\Auth\RegisterRequest;

class RegisterMapper
{
    public static function fromRequest(RegisterRequest $request): RegisterUserDTO
    {
        return new RegisterUserDTO(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone')
        );
    }
}
