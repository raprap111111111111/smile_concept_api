<?php

use App\Http\Controllers\v1\PrescriptionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('prescriptions', PrescriptionController::class);
