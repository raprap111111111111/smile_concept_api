<?php

namespace App\Domain\Settings\Actions;

use App\Domain\Settings\DTOs\BulkUpdateSettingDTO;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkUpdateSettingAction
{
    public function __construct(
        private readonly SettingService $service,
    ) {}

    public function execute(BulkUpdateSettingDTO $dto): Collection
    {
        return DB::transaction(fn() => $this->service->bulkSet($dto->settings));
    }
}