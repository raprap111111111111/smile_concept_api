<?php

namespace App\Http\Controllers\v1;

use App\Domain\ActivityLogs\Repositories\ActivityLogRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\ActivityLog\GetActivityLogRequest;
use App\Http\Requests\v1\ActivityLog\GetAllActivityLogsRequest;
use App\Http\Resources\v1\ActivityLogResource;
use App\Models\ActivityLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ActivityLogRepository $repository,
    ) {}

    public function index(GetAllActivityLogsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), ActivityLogResource::class);
        return $this->successResponse($result, 'Activity logs retrieved.');
    }

    public function show(GetActivityLogRequest $request, ActivityLog $activityLog): JsonResponse
    {
        return $this->successResponse(
            new ActivityLogResource($activityLog->load('user')),
            'Activity log details retrieved.'
        );
    }
}