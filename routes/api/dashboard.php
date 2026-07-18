<?php

use App\Http\Controllers\v1\DashboardController;
use Illuminate\Support\Facades\Route;

// Clinic-wide revenue and patient metrics — the client-side route guard hides
// the page, but the endpoint must refuse the data on its own.
Route::get('dashboard/analytics', [DashboardController::class, 'index'])
    ->middleware('permission:dashboard.view');
