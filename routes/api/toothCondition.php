<?php

use App\Http\Controllers\v1\ToothConditionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tooth-conditions', ToothConditionController::class);