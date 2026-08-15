<?php

use App\Http\Controllers\v1\InventorySettingController;
use Illuminate\Support\Facades\Route;

// One form, one resource — no apiResource. Mirrors routes/api/appointmentSetting.php.
Route::get('inventory-settings', [InventorySettingController::class, 'show'])
    ->name('inventory-settings.show');

Route::put('inventory-settings', [InventorySettingController::class, 'update'])
    ->name('inventory-settings.update');
