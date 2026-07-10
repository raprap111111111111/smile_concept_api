<?php

use App\Http\Controllers\v1\InventoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('inventories', InventoryController::class);
