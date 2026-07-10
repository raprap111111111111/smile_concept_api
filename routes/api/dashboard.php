<?php

use App\Http\Controllers\v1\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard/analytics', [DashboardController::class, 'index']);
