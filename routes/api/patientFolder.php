<?php

use App\Http\Controllers\v1\PatientFolderController;
use Illuminate\Support\Facades\Route;

// ✅ Dedicated Patient Folder Routes
Route::prefix('patient-folders')->group(function () {
    Route::get('/', [PatientFolderController::class, 'index'])
        ->name('patient-folders.index');
    
    Route::get('/{userId}', [PatientFolderController::class, 'show'])
        ->whereNumber('userId')
        ->name('patient-folders.show');
});