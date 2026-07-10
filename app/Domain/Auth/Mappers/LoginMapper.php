<?php

namespace App\Domain\Auth\Mappers;

use App\Http\Requests\v1\Auth\LoginRequest;
use App\Domain\Auth\DTOs\LoginDTO;

class LoginMapper
{
    /**
     * Map the incoming HTTP validation request layer into a pure Domain DTO
     */
    public static function fromRequest(LoginRequest $request): LoginDTO
    {
        return new LoginDTO(
            email: $request->input('email'),
            password: $request->input('password')
        );
    }
}