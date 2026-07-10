<?php

namespace App\Domain\Settings\Mappers;

use App\Domain\Settings\DTOs\BulkUpdateSettingDTO;
use App\Domain\Settings\DTOs\UpdateSettingDTO;
use App\Http\Requests\v1\Setting\BulkUpdateSettingRequest;
use App\Http\Requests\v1\Setting\UpdateSettingRequest;

class SettingMapper
{
    public static function fromUpdateRequest(UpdateSettingRequest $request, string $key): UpdateSettingDTO
    {
        return new UpdateSettingDTO(
            key:   $key,
            value: $request->validated('value'),
        );
    }

    public static function fromBulkRequest(BulkUpdateSettingRequest $request): BulkUpdateSettingDTO
    {
        return new BulkUpdateSettingDTO(
            settings: $request->validated('settings', []),
        );
    }
}