<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode'])
        ->middleware('throttle:5,1');
    Route::post('/verify-code', [AuthController::class, 'verifyCode'])
        ->middleware('throttle:10,1');
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Onboarding
    Route::post('/auth/onboarding', [AuthController::class, 'completeOnboarding']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Routes requiring completed onboarding
    Route::middleware('onboarding')->group(function () {
        Route::get('/search', SearchController::class);
    });
});
