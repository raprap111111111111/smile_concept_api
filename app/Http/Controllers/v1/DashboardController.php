<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Domain\Dashboards\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service
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
}
