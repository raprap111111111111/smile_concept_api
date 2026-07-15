<?php

use App\Http\Controllers\v1\TreatmentPlanController;
use Illuminate\Support\Facades\Route;

Route::patch(
    'treatment-plans/{treatmentPlan}/status',
    [TreatmentPlanController::class, 'changeStatus']
)->name('treatment-plans.change-status');

Route::apiResource('treatment-plans', TreatmentPlanController::class);