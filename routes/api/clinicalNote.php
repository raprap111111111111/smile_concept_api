<?php

use App\Http\Controllers\v1\ClinicalNoteController;
use Illuminate\Support\Facades\Route;

Route::apiResource('clinical-notes', ClinicalNoteController::class);
