<?php

use App\Http\Controllers\v1\DashboardController;
use Illuminate\Support\Facades\Route;

// Clinic-wide revenue and patient metrics — the client-side route guard hides
// the page, but the endpoint must refuse the data on its own.
Route::middleware('permission:dashboard.view')->group(function () {
    Route::get('dashboard/analytics', [DashboardController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/appointments-today', [DashboardController::class, 'todaySchedule']);
    Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
});
