<?php

use App\Http\Controllers\v1\AppointmentTreatmentController;
use Illuminate\Support\Facades\Route;

    Route::apiResource('appointment-treatments', AppointmentTreatmentController::class);