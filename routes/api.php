<?php

use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\SettingController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |------------------------
    | AUTH (Public)
    |------------------------
    */
    // routes/api.php (public group)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('refresh', [AuthController::class, 'refresh']); // ✅ NEW
        Route::post('social', [AuthController::class, 'socialLogin']);
    });

    /*
    |------------------------
    | AUTH (Protected)
    |------------------------
    */
    Route::middleware('auth:api')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('update-password', [AuthController::class, 'updatePassword']);
        });

        Route::get('profile', [AuthController::class, 'profile']);

        Route::get('users/me', [UserController::class, 'me'])->name('users.me');


        /*
        |------------------------
        | MODULES
        |------------------------
        */
        require __DIR__ . '/api/branch.php';
        require __DIR__ . '/api/role.php';
        require __DIR__ . '/api/permission.php';
        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/doctor.php';
        require __DIR__ . '/api/appointment.php';
        require __DIR__ . '/api/appointmentTreatment.php';   // ✅ ADDED
        require __DIR__ . '/api/appointmentReminder.php';    // ✅ ADDED
        require __DIR__ . '/api/toothCondition.php';
        require __DIR__ . '/api/dentalChart.php';
        require __DIR__ . '/api/doctorSchedule.php';
        require __DIR__ . '/api/patientProfile.php';
        require __DIR__ . '/api/invoices.php';
        require __DIR__ . '/api/treatment.php';
        require __DIR__ . '/api/dentalChartEntry.php';
        require __DIR__ . '/api/payment.php';
        require __DIR__ . '/api/invoiceItem.php';
        require __DIR__ . '/api/prescription.php';
        require __DIR__ . '/api/item.php';
        require __DIR__ . '/api/inventory.php';
        require __DIR__ . '/api/patientPortal.php';
        require __DIR__ . '/api/treatmentPlan.php';
        require __DIR__ . '/api/recalls.php';
        require __DIR__ . '/api/recallType.php';
        require __DIR__ . '/api/dashboard.php';
        require __DIR__ . '/api/patientAttachment.php';
        require __DIR__ . '/api/labCase.php';
        require __DIR__ . '/api/clinicalNote.php';
        require __DIR__ . '/api/consents.php';
        require __DIR__ . '/api/setting.php';
        require __DIR__ . '/api/activityLog.php';
        require __DIR__ . '/api/notification.php';
        require __DIR__ . '/api/notificationTemplate.php';
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC (No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('/services', [ServiceController::class, 'publicIndex']);
    Route::get('/gallery', [GalleryController::class, 'publicIndex']);
    Route::get('/settings', [SettingController::class, 'publicIndex']);
});

/*
|--------------------------------------------------------------------------
| ADMIN (Passport Protected)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('gallery', GalleryController::class);
    Route::post('/gallery/bulk-delete', [GalleryController::class, 'bulkDelete']);
});
