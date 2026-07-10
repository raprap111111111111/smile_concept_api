<?php

use App\Http\Controllers\v1\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('treatments', TreatmentController::class);
