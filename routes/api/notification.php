<?php

use App\Http\Controllers\v1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function () {
    Route::get('/',                    [NotificationController::class, 'index']);
    Route::get('unread-count',         [NotificationController::class, 'unreadCount']);
    Route::post('{id}/read',           [NotificationController::class, 'markAsRead']);
    Route::post('mark-all-read',       [NotificationController::class, 'markAllAsRead']);
    Route::delete('{id}',              [NotificationController::class, 'destroy']);
});