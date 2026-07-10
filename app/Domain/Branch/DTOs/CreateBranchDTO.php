<?php

namespace App\Domain\Branch\DTOs;

final readonly class CreateBranchDTO
{
    public function __construct(
        public string $name,
        public ?string $branchCode = null,
        public string $address,
        public ?string $city,
        public ?string $province,
        public ?string $phone,
        public ?string $email,
        public bool $isActive,
        public ?string $openingHours,
    ) {}
}