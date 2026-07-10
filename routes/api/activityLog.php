<?php

use App\Http\Controllers\v1\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::apiResource('activity-logs', ActivityLogController::class)
    ->only(['index', 'show']);