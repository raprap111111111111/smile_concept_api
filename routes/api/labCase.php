<?php

use App\Http\Controllers\v1\LabCaseController;
use Illuminate\Support\Facades\Route;

Route::apiResource('lab-cases', LabCaseController::class);
