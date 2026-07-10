<?php

use App\Http\Controllers\v1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payments', PaymentController::class);