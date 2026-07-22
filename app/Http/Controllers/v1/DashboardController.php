<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Domain\Dashboards\Services\DashboardService;
use App\Domain\Dashboards\Services\DashboardMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
        private readonly DashboardMetricsService $metrics,
    ) {}

    /**
     * Retrieve global clinical and financial dashboard metrics
     */
    public function index(): JsonResponse
    {
        try {
            $summary = $this->service->getAnalyticsSummary();
            return $this->successResponse($summary, 'Dashboard analytical summary compiled.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Headline counters plus the trend series the dashboard charts render.
     */
    public function stats(): JsonResponse
    {
        try {
            return $this->successResponse(
                $this->metrics->getStats(),
                'Dashboard statistics retrieved.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Today's appointment list with hourly and status breakdowns.
     */
    public function todaySchedule(): JsonResponse
    {
        try {
            return $this->successResponse(
                $this->metrics->getTodaySchedule(),
                "Today's schedule retrieved."
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Recent activity log entries with per-type and per-day breakdowns.
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min($limit, 50));

        try {
            return $this->successResponse(
                $this->metrics->getRecentActivity($limit),
                'Recent activity retrieved.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
