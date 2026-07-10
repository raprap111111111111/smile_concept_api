<?php

namespace App\Domain\Settings\DTOs;

final readonly class UpdateSettingDTO
{
    public function __construct(
        public string $key,
        public mixed  $value,
    ) {}
}