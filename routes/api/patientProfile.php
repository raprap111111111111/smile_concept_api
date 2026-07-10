<?php

use App\Http\Controllers\v1\PatientProfileController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════
//  PATIENT PROFILES — Medical info only (self-service + admin)
// ══════════════════════════════════════════════════════════

// Self-service: logged-in patient views/updates their own profile
Route::get('patient-profiles/me', [PatientProfileController::class, 'me']);

// Admin: view/update existing profiles (NO create, NO delete)
Route::apiResource('patient-profiles', PatientProfileController::class)
    ->only(['index', 'show', 'update']);


// ══════════════════════════════════════════════════════════
//  PATIENTS — Full CRUD (User + Profile together) — ADMIN ONLY
// ══════════════════════════════════════════════════════════

Route::apiResource('patients', PatientProfileController::class);