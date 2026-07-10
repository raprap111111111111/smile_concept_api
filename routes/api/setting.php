<?php

use App\Http\Controllers\v1\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
|  SETTINGS
|--------------------------------------------------------------------------
*/

// Admin CRUD
Route::get('settings',                    [SettingController::class, 'index']);
Route::get('settings/{key}',              [SettingController::class, 'show']);
Route::put('settings/{key}',              [SettingController::class, 'update']);
Route::post('settings/bulk-update',       [SettingController::class, 'bulkUpdate']);