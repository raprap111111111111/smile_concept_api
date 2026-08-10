<?php

use App\Http\Controllers\v1\ConsentTemplateController;
use App\Http\Controllers\v1\PatientConsentController;
use Illuminate\Support\Facades\Route;

Route::prefix('consents/templates')->group(function () {
    Route::get('/', [ConsentTemplateController::class, 'index']);
    Route::get('{template}', [ConsentTemplateController::class, 'show']);
});

Route::prefix('consents')->group(function () {
    Route::get('/', [PatientConsentController::class, 'index']);
    Route::post('sign', [PatientConsentController::class, 'sign']);
    Route::get('{consent}', [PatientConsentController::class, 'show']);
    Route::get('{consent}/pdf', [PatientConsentController::class, 'pdf']);
    Route::get('{consent}/download', [PatientConsentController::class, 'download']);
    Route::post('{consent}/void', [PatientConsentController::class, 'void']);

    Route::get(
        'appointments/{appointment}/consents',
        [PatientConsentController::class, 'byAppointment']
    );
});
