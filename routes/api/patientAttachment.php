<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\PatientAttachmentController;

Route::prefix('patient-attachments')->group(function () {

    // ✅ Specific routes FIRST
    Route::get('/patients', [PatientAttachmentController::class, 'patients']);
    Route::get('/patients/{userId}', [PatientAttachmentController::class, 'byPatient'])
        ->whereNumber('userId');

    // ✅ File streaming (authenticated)
    Route::get('/{patientAttachment}/file', [PatientAttachmentController::class, 'file'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.file');

    Route::get('/{patientAttachment}/download', [PatientAttachmentController::class, 'download'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.download');

    // Standard CRUD
    Route::get('/', [PatientAttachmentController::class, 'index']);
    Route::post('/', [PatientAttachmentController::class, 'store']);
    Route::get('/{patientAttachment}', [PatientAttachmentController::class, 'show']);
    Route::put('/{patientAttachment}', [PatientAttachmentController::class, 'update']);
    Route::delete('/{patientAttachment}', [PatientAttachmentController::class, 'destroy']);
});
