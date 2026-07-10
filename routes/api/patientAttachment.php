<?php

use App\Http\Controllers\v1\PatientAttachmentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('patient-attachments', PatientAttachmentController::class);
