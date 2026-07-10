<?php

use App\Http\Controllers\v1\DentalChartEntryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dental-chart-entries', DentalChartEntryController::class);
