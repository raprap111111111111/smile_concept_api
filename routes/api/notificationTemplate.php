<?php

use App\Http\Controllers\v1\NotificationTemplateController;
use Illuminate\Support\Facades\Route;

Route::apiResource('notification-templates', NotificationTemplateController::class);

Route::post('notifications/test-email', [NotificationTemplateController::class, 'testEmail']);