<?php

namespace App\Domain\Settings\DTOs;

final readonly class BulkUpdateSettingDTO
{
    /**
     * @param array<string, mixed> $settings [ key => value ]
     */
    public function __construct(
        public array $settings,
    ) {}
}