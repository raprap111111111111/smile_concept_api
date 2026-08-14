<?php

use App\Http\Controllers\v1\AppointmentSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
|  APPOINTMENT SETTINGS
|--------------------------------------------------------------------------
| One typed GET/PUT pair for the whole appointment-settings form.
| Values persist in the generic `settings` table.
*/

Route::get('appointment-settings', [AppointmentSettingController::class, 'show']);
Route::put('appointment-settings', [AppointmentSettingController::class, 'update']);
