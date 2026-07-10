<?php

namespace App\Domain\Auth\DTOs;

final readonly class SocialLoginDTO
{
    public function __construct(
        public string $provider,
        public string $token
    ) {}
}
