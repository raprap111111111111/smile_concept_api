<?php

namespace App\Http\Controllers\v1;

use App\Domain\ActivityLogs\Repositories\ActivityLogRepository;
use App\Http\Controllers\Controller;
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

    /**
     * List paginated activity logs.
     */
    public function index(GetAllActivityLogsRequest $request): JsonResponse
    {
        $logs = $this->repository->paginate(
            $request->validated(),
            ActivityLogResource::class
        );

        return $this->successResponse($logs, 'Activity logs retrieved.');
    }

    /**
     * Show a single activity log with user details.
     */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        // ✅ Removed unused $request parameter
        // ✅ Eager load user relationship
        $activityLog->load('user');

        return $this->successResponse(
            new ActivityLogResource($activityLog),
            'Activity log details retrieved.'
        );
    }
}