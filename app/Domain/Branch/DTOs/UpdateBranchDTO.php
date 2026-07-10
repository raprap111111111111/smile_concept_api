<?php

namespace App\Domain\Branch\DTOs;

final readonly class UpdateBranchDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $branchCode = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?bool $isActive = null,
        public ?string $openingHours = null,
    ) {}
}