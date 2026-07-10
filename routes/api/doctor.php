<?php

use App\Http\Controllers\v1\DoctorController;
use Illuminate\Support\Facades\Route;

Route::apiResource('doctors', DoctorController::class);