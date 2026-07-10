<?php

use App\Http\Controllers\v1\RoleController;
use Illuminate\Support\Facades\Route;

Route::post('roles/{role}/permissions/sync', [RoleController::class, 'syncPermissions']);
Route::post('roles/{role}/permissions/assign', [RoleController::class, 'assignPermission']);
Route::delete('roles/{role}/permissions/{permission}', [RoleController::class, 'removePermission']);

Route::apiResource('roles', RoleController::class);