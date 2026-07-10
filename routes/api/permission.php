<?php

use App\Http\Controllers\v1\PermissionController;
use Illuminate\Support\Facades\Route;

Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
Route::apiResource('permissions', PermissionController::class);