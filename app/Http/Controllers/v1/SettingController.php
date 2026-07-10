<?php

namespace App\Http\Controllers\v1;

use App\Domain\Settings\Actions\BulkUpdateSettingAction;
use App\Domain\Settings\Actions\UpdateSettingAction;
use App\Domain\Settings\Mappers\SettingMapper;
use App\Domain\Settings\Repositories\SettingRepository;
use App\Domain\Settings\Services\SettingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Setting\BulkUpdateSettingRequest;
use App\Http\Requests\v1\Setting\GetAllSettingsRequest;
use App\Http\Requests\v1\Setting\GetSettingRequest;
use App\Http\Requests\v1\Setting\UpdateSettingRequest;
use App\Http\Resources\v1\SettingResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SettingRepository       $repository,
        private readonly SettingService          $service,
        private readonly UpdateSettingAction     $updateAction,
        private readonly BulkUpdateSettingAction $bulkAction,
    ) {}

    /**
     * List all settings (admin).
     */
    public function index(GetAllSettingsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), SettingResource::class);
        return $this->successResponse($result, 'Settings retrieved.');
    }

    /**
     * Public settings (safe for frontend, no auth needed if opened publicly).
     */
    public function publicIndex(): JsonResponse
    {
        return $this->successResponse(
            $this->service->publicSettings(),
            'Public settings retrieved.'
        );
    }

    /**
     * Get one setting by key.
     */
    public function show(GetSettingRequest $request, string $key): JsonResponse
    {
        $setting = $this->repository->findByKey($key);

        if (!$setting) {
            return $this->errorResponse("Setting [{$key}] not found.", 404);
        }

        return $this->successResponse(
            new SettingResource($setting),
            'Setting retrieved.'
        );
    }

    /**
     * Update a single setting.
     */
    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        try {
            $setting = $this->updateAction->execute(
                SettingMapper::fromUpdateRequest($request, $key)
            );

            return $this->successResponse(
                new SettingResource($setting),
                'Setting updated successfully.'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(BulkUpdateSettingRequest $request): JsonResponse
    {
        try {
            $updated = $this->bulkAction->execute(
                SettingMapper::fromBulkRequest($request)
            );

            return $this->successResponse(
                SettingResource::collection($updated),
                "Successfully updated {$updated->count()} setting(s)."
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}