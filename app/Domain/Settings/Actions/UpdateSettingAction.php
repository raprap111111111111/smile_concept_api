<?php

namespace App\Domain\Settings\Actions;

use App\Domain\Settings\DTOs\UpdateSettingDTO;
use App\Domain\Settings\Services\SettingService;
use App\Models\Setting;

class UpdateSettingAction
{
    public function __construct(
        private readonly SettingService $service,
    ) {}

    public function execute(UpdateSettingDTO $dto): Setting
    {
        return $this->service->set($dto->key, $dto->value);
    }
}