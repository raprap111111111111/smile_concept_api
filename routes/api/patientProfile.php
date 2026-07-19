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

// Same controller as `patient-profiles`, so the route parameter has to carry
// the same name — every FormRequest here reads `route('patient_profile')`, and
// the controller typehints `$patientProfile`. Left as the default `{patient}`,
// both the policy lookup and the implicit model binding silently resolve to
// null and every write 403s.
Route::apiResource('patients', PatientProfileController::class)
    ->parameters(['patients' => 'patient_profile']);