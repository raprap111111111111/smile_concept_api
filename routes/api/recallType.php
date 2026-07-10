<?php

use App\Http\Controllers\v1\RecallTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('recall-types', RecallTypeController::class);
