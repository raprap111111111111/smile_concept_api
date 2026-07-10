<?php

use App\Http\Controllers\v1\PatientPortalController;
use Illuminate\Support\Facades\Route;

Route::get('patient/dashboard', [PatientPortalController::class, 'dashboard']);
