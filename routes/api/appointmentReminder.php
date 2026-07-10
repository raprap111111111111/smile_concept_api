<?php

use App\Http\Controllers\v1\AppointmentReminderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
|  APPOINTMENT REMINDERS — Read-only log
|--------------------------------------------------------------------------
|  Reminders are auto-created by ScheduleReminderAction and dispatched
|  by the scheduler. Users can only view logs.
*/

Route::apiResource('appointment-reminders', AppointmentReminderController::class)
    ->only(['index', 'show']);