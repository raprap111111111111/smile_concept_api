<?php

use App\Http\Controllers\v1\DentalChartController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dental-charts', DentalChartController::class);