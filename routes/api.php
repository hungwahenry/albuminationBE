<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GiphyController;
use App\Http\Controllers\LoveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RotationCommentController;
use App\Http\Controllers\RotationController;
use App\Http\Controllers\RotationItemController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TakeController;
use App\Http\Controllers\TakeReactionController;
use App\Http\Controllers\TakeReplyController;
use App\Http\Controllers\TrackFavouriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowController;
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

        // Giphy proxy
        Route::prefix('giphy')->group(function () {
            Route::get('/trending', [GiphyController::class, 'trending']);
            Route::get('/search', [GiphyController::class, 'search']);
        });
        Route::get('/albums/{mbid}', [AlbumController::class, 'show']);
        Route::post('/albums/{mbid}/love', [LoveController::class, 'toggleAlbum']);
        Route::post('/albums/{mbid}/tracks/{track}/favourite', [TrackFavouriteController::class, 'toggle']);

        // Takes
        Route::prefix('albums/{mbid}/takes')->group(function () {
            Route::get('/', [TakeController::class, 'index']);
            Route::post('/', [TakeController::class, 'store']);
            Route::put('/{take}', [TakeController::class, 'update']);
            Route::delete('/{take}', [TakeController::class, 'destroy']);
            Route::post('/{take}/react', [TakeReactionController::class, 'react']);

            // Replies
            Route::get('/{take}/replies', [TakeReplyController::class, 'index']);
            Route::post('/{take}/replies', [TakeReplyController::class, 'store']);
            Route::delete('/{take}/replies/{reply}', [TakeReplyController::class, 'destroy']);
            Route::post('/{take}/replies/{reply}/love', [LoveController::class, 'toggleReply']);
        });

        // Rotations
        Route::prefix('rotations')->group(function () {
            Route::get('/', [RotationController::class, 'index']);
            Route::post('/', [RotationController::class, 'store']);
            Route::get('/{rotation}', [RotationController::class, 'show']);
            Route::put('/{rotation}', [RotationController::class, 'update']);
            Route::delete('/{rotation}', [RotationController::class, 'destroy']);
            Route::post('/{rotation}/publish', [RotationController::class, 'publish']);
            Route::post('/{rotation}/redraft', [RotationController::class, 'redraft']);
            Route::post('/{rotation}/items', [RotationItemController::class, 'store']);
            Route::delete('/{rotation}/items/{item}', [RotationItemController::class, 'destroy']);
            Route::post('/{rotation}/items/reorder', [RotationItemController::class, 'reorder']);

            // Love
            Route::post('/{rotation}/love', [LoveController::class, 'toggleRotation']);

            // Comments
            Route::get('/{rotation}/comments', [RotationCommentController::class, 'index']);
            Route::post('/{rotation}/comments', [RotationCommentController::class, 'store']);
            Route::get('/{rotation}/comments/{comment}/replies', [RotationCommentController::class, 'replies']);
            Route::delete('/{rotation}/comments/{comment}', [RotationCommentController::class, 'destroy']);
            Route::post('/{rotation}/comments/{comment}/love', [LoveController::class, 'toggleComment']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/reasons', [ReportController::class, 'reasons']);
            Route::post('/', [ReportController::class, 'store']);
        });

        // Profile
        Route::put('/profile', [ProfileController::class, 'update']);

        // Users (public profiles)
        Route::prefix('users/{username}')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::get('/rotations', [ProfileController::class, 'rotations']);
            Route::post('/follow', [FollowController::class, 'toggle']);
            Route::get('/followers', [FollowController::class, 'followers']);
            Route::get('/following', [FollowController::class, 'following']);
        });
    });
});
