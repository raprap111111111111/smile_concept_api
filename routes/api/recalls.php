<?php

use App\Http\Controllers\v1\RecallController;
use Illuminate\Support\Facades\Route;

Route::apiResource('recalls', RecallController::class);
