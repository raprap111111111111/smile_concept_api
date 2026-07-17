<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\ClinicalRecordsController;

// Inside your v1 auth-protected group:
Route::prefix('clinical-records')->group(function () {
    Route::get('/summary', [ClinicalRecordsController::class, 'summary']);
    Route::get('/patients/{patientId}/summary', [ClinicalRecordsController::class, 'patientSummary']);
});