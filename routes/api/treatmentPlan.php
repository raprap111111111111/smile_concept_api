<?php

use App\Http\Controllers\v1\TreatmentPlanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('treatment-plans', TreatmentPlanController::class);
