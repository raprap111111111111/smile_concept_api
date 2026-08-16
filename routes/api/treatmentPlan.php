<?php

use App\Http\Controllers\v1\TreatmentPlanConsumableController;
use App\Http\Controllers\v1\TreatmentPlanController;
use Illuminate\Support\Facades\Route;

Route::patch(
    'treatment-plans/{treatment_plan}/status',
    [TreatmentPlanController::class, 'changeStatus']
)->name('treatment-plans.change-status');

// Supplies used for a completed plan. Above apiResource so the literal
// segment is not swallowed as an id.
Route::get(
    'treatment-plans/{treatment_plan}/consumables',
    [TreatmentPlanConsumableController::class, 'index']
)->name('treatment-plans.consumables.index');

Route::post(
    'treatment-plans/{treatment_plan}/consumables',
    [TreatmentPlanConsumableController::class, 'store']
)->name('treatment-plans.consumables.store');

Route::apiResource('treatment-plans', TreatmentPlanController::class);