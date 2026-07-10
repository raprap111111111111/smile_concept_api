<?php

use App\Http\Controllers\v1\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show', 'store']);
