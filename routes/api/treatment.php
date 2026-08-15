<?php

use App\Http\Controllers\v1\TreatmentConsumableController;
use App\Http\Controllers\v1\TreatmentController;
use Illuminate\Support\Facades\Route;

// Nested under the treatment because a recipe has no meaning apart from it.
// Above apiResource, so `consumables` is never swallowed as a {treatment} id.
Route::get('treatments/{treatment}/consumables', [TreatmentConsumableController::class, 'index'])
    ->name('treatments.consumables.index');

// A whole-list save: the form panel edits every line at once, and an empty
// array clears the recipe.
Route::put('treatments/{treatment}/consumables', [TreatmentConsumableController::class, 'sync'])
    ->name('treatments.consumables.sync');

Route::apiResource('treatments', TreatmentController::class);
