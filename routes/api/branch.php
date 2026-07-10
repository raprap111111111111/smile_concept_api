<?php

use App\Http\Controllers\v1\BranchController;
use Illuminate\Support\Facades\Route;

Route::apiResource('branches', BranchController::class);