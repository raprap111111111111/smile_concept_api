<?php

use App\Http\Controllers\v1\ItemController;
use Illuminate\Support\Facades\Route;

Route::apiResource('items', ItemController::class);
