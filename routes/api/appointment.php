<?php

use App\Http\Controllers\v1\AppointmentController;
use Illuminate\Support\Facades\Route;

// ✅ Custom routes should come BEFORE apiResource to avoid conflicts
Route::get('appointments/available-slots', [AppointmentController::class, 'availableSlots']);

// ✅ PATCH route for updating ONLY status (with permission)
Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
    ->name('appointments.status.update');

Route::get('appointments/calendar-counts', [AppointmentController::class, 'calendarCounts']);

// Resource routes
Route::apiResource('appointments', AppointmentController::class);
