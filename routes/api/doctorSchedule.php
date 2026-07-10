<?php
use App\Http\Controllers\v1\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

// Notice: No prefix('v1') here, because it's already wrapped in routes/api.php
Route::apiResource('doctor-schedules', DoctorScheduleController::class);