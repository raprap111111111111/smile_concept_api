<?php

use App\Http\Controllers\v1\InvoiceItemController;
use Illuminate\Support\Facades\Route;

Route::apiResource('invoice-items', InvoiceItemController::class);