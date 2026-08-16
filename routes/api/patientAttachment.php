<?php

use App\Http\Controllers\v1\PatientAttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('patient-attachments')->group(function () {

    // ─── Specific routes FIRST ──────────────────────────────
    Route::get('/patients', [PatientAttachmentController::class, 'patients']);
    Route::get('/patients/{userId}', [PatientAttachmentController::class, 'byPatient'])
        ->whereNumber('userId');

    // ─── Token generator (authenticated) ────────────────────
    // ✅ Frontend calls this to get a short-lived access URL
    Route::get('/{patientAttachment}/access-url', [PatientAttachmentController::class, 'accessUrl'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.access-url');

    // ─── File streaming — token-based, no auth needed ───────
    // ✅ These work in browser tabs, <img> tags, print dialogs
    Route::get('/file/{token}', [PatientAttachmentController::class, 'fileByToken'])
        ->name('patient-attachments.file-by-token')
        ->withoutMiddleware(['auth:api']);

    Route::get('/download/{token}', [PatientAttachmentController::class, 'downloadByToken'])
        ->name('patient-attachments.download-by-token')
        ->withoutMiddleware(['auth:api']);

    // ─── Legacy authenticated routes (kept for backward compat) ──
    Route::get('/{patientAttachment}/file', [PatientAttachmentController::class, 'file'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.file');

    Route::get('/{patientAttachment}/download', [PatientAttachmentController::class, 'download'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.download');

    // ─── Print audit log ────────────────────────────────────
    Route::post('/{patientAttachment}/print-log', [PatientAttachmentController::class, 'logPrint'])
        ->whereNumber('patientAttachment')
        ->name('patient-attachments.print-log');

    // ─── Standard CRUD ──────────────────────────────────────
    Route::get('/', [PatientAttachmentController::class, 'index']);
    Route::post('/', [PatientAttachmentController::class, 'store']);
    Route::get('/{patientAttachment}', [PatientAttachmentController::class, 'show']);
    Route::put('/{patientAttachment}', [PatientAttachmentController::class, 'update']);
    Route::delete('/{patientAttachment}', [PatientAttachmentController::class, 'destroy']);
});