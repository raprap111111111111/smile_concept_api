<?php

use App\Http\Controllers\v1\ConsentController;
use Illuminate\Support\Facades\Route;

Route::get('consent/templates', [ConsentController::class, 'templates']);
Route::post('consent/sign', [ConsentController::class, 'sign']);
