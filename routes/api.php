<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\CoverController;
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
use App\Http\Controllers\ProfileCustomizationController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\UsernameController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\VibetagController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/covers', CoverController::class);

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode'])
        ->middleware('throttle:auth.send');
    Route::post('/verify-code', [AuthController::class, 'verifyCode'])
        ->middleware('throttle:auth.verify');
});

// Authenticated routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Onboarding
    Route::post('/auth/onboarding', [AuthController::class, 'completeOnboarding']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/username/check', [UsernameController::class, 'check']);

    // Account settings (email change, delete account)
    Route::prefix('account')->group(function () {
        Route::post('/email/send-code', [AccountController::class, 'sendEmailChangeCode'])->middleware('throttle:auth.send');
        Route::post('/email/verify', [AccountController::class, 'verifyEmailChange']);
        Route::post('/delete/send-code', [AccountController::class, 'sendDeletionCode'])->middleware('throttle:auth.send');
        Route::delete('/', [AccountController::class, 'deleteAccount']);
    });

    // Badges
    Route::get('/me/badges', [BadgeController::class, 'mine']);
    Route::get('/users/{username}/badges', [BadgeController::class, 'forUser']);

    // Data export
    Route::get('/account/export', ExportController::class);
    Route::get('/account/blocked', [BlockController::class, 'index']);

    // Routes requiring completed onboarding
    Route::middleware('onboarding')->group(function () {
        Route::get('/search', SearchController::class)->middleware('throttle:search');

        // Feed
        Route::prefix('feed')->group(function () {
            Route::get('/sections', [FeedController::class, 'sections']);
            Route::get('/sections/{type}', [FeedController::class, 'sectionData']);
        });

        // Giphy proxy
        Route::prefix('giphy')->middleware('throttle:giphy')->group(function () {
            Route::get('/trending', [GiphyController::class, 'trending']);
            Route::get('/search', [GiphyController::class, 'search']);
        });
        Route::get('/artists/{slug}', [ArtistController::class, 'show']);
        Route::post('/artists/{slug}/view', [ViewController::class, 'storeArtistView']);
        Route::get('/artists/{slug}/albums', [ArtistController::class, 'albums']);
        Route::post('/artists/{slug}/stan', [ArtistController::class, 'stan']);

        Route::get('/albums/{slug}', [AlbumController::class, 'show']);
        Route::post('/albums/{slug}/view', [ViewController::class, 'storeAlbumView']);
        Route::post('/albums/{slug}/love', [LoveController::class, 'toggleAlbum']);
        Route::post('/albums/{slug}/tracks/{track}/favourite', [TrackFavouriteController::class, 'toggle']);

        // Takes
        Route::prefix('albums/{slug}/takes')->group(function () {
            Route::get('/', [TakeController::class, 'index']);
            Route::post('/', [TakeController::class, 'store'])->middleware('throttle:writes');
            Route::get('/{take}', [TakeController::class, 'show']);
            Route::put('/{take}', [TakeController::class, 'update']);
            Route::delete('/{take}', [TakeController::class, 'destroy']);
            Route::post('/{take}/react', [TakeReactionController::class, 'react']);

            // Replies
            Route::get('/{take}/replies', [TakeReplyController::class, 'index']);
            Route::post('/{take}/replies', [TakeReplyController::class, 'store'])->middleware('throttle:writes');
            Route::delete('/{take}/replies/{reply}', [TakeReplyController::class, 'destroy']);
            Route::post('/{take}/replies/{reply}/love', [LoveController::class, 'toggleReply']);
        });

        // Rotations
        Route::prefix('rotations')->group(function () {
            Route::get('/', [RotationController::class, 'index']);
            Route::post('/', [RotationController::class, 'store'])->middleware('throttle:writes');
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

            // Views
            Route::post('/{rotation}/view', [ViewController::class, 'storeRotationView']);

            // Comments
            Route::get('/{rotation}/comments', [RotationCommentController::class, 'index']);
            Route::post('/{rotation}/comments', [RotationCommentController::class, 'store'])->middleware('throttle:writes');
            Route::get('/{rotation}/comments/{comment}/replies', [RotationCommentController::class, 'replies']);
            Route::delete('/{rotation}/comments/{comment}', [RotationCommentController::class, 'destroy']);
            Route::post('/{rotation}/comments/{comment}/love', [LoveController::class, 'toggleComment']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::get('/reasons', [ReportController::class, 'reasons']);
            Route::post('/', [ReportController::class, 'store'])->middleware('throttle:writes');
        });

        // Profile
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::delete('/profile/followers/{username}', [FollowController::class, 'removeFollower']);

        // Notification preferences
        Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
        Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);

        // Device tokens for push notifications
        Route::post('/notifications/devices', [DeviceTokenController::class, 'store']);
        Route::delete('/notifications/devices', [DeviceTokenController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Profile Customization
        Route::prefix('profile/customization')->group(function () {
            Route::put('/header-album', [ProfileCustomizationController::class, 'setHeaderAlbum']);
            Route::put('/pinned-rotation', [ProfileCustomizationController::class, 'setPinnedRotation']);
            Route::put('/current-vibe', [ProfileCustomizationController::class, 'setCurrentVibe']);
        });

        // Vibetags
        Route::prefix('vibetags/{name}')->group(function () {
            Route::get('/', [VibetagController::class, 'show']);
            Route::get('/rotations', [VibetagController::class, 'rotations']);
        });

        // Users (public profiles)
        Route::prefix('users/{username}')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::post('/view', [ViewController::class, 'storeProfileView']);
            Route::get('/rotations', [ProfileController::class, 'rotations']);
            Route::get('/takes', [ProfileController::class, 'takes']);
            Route::post('/follow', [FollowController::class, 'toggle']);
            Route::get('/followers', [FollowController::class, 'followers']);
            Route::get('/following', [FollowController::class, 'following']);
            Route::post('/block', [BlockController::class, 'store']);
            Route::delete('/block', [BlockController::class, 'destroy']);
        });
    });
});
