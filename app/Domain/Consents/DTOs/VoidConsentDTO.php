<?php

namespace App\Domain\Consents\DTOs;

final readonly class VoidConsentDTO
{
    public function __construct(
        public string  $reason,
        public int     $voidedBy,    
        public ?string $ipAddress,
        public ?string $userAgent,
    ) {}
}