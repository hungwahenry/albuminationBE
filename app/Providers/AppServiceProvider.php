<?php

namespace App\Providers;

use App\Models\Love;
use App\Models\Profile;
use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use App\Models\TrackFavourite;
use App\Observers\ProfileObserver;
use App\Observers\TakeObserver;
use App\Observers\TrackFavouriteObserver;
use App\Services\Badge\ActionRegistry;
use App\Services\MusicBrainz\MusicBrainzClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MusicBrainzClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Take::observe(TakeObserver::class);
        TrackFavourite::observe(TrackFavouriteObserver::class);
        Profile::observe(ProfileObserver::class);

        $this->bootBadgeActions();

        // Global authenticated API — 120 requests/minute per user
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints — per IP to prevent enumeration/brute force
        RateLimiter::for('auth.send', function (Request $request) {
            return Limit::perMinutes(5, 3)->by($request->ip());
        });

        RateLimiter::for('auth.verify', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        // Content creation — 30 writes/minute per user to prevent spam
        RateLimiter::for('writes', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Giphy proxy — protect the API key, 20 requests/minute per user
        RateLimiter::for('giphy', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Search — prevent scraping, 30 requests/minute per user
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Admin login — 5 attempts per 10 minutes per IP
        RateLimiter::for('admin.login', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });
    }

    private function bootBadgeActions(): void
    {
        ActionRegistry::register(
            'loves_given',
            fn ($user) => Love::where('user_id', $user->id)->count(),
        );

        ActionRegistry::register(
            'loves_received',
            fn ($user) => Love::whereHasMorph('loveable', '*', fn ($q) => $q->where('user_id', $user->id))->count(),
        );

        ActionRegistry::register(
            'rotation_comments',
            fn ($user) => RotationComment::where('user_id', $user->id)->count(),
        );

        ActionRegistry::register(
            'take_replies',
            fn ($user) => TakeReply::where('user_id', $user->id)->count(),
        );

        ActionRegistry::register(
            'unique_vibetags',
            fn ($user) => DB::table('taggables')
                ->join('rotations', function ($join) {
                    $join->on('rotations.id', '=', 'taggables.taggable_id')
                         ->where('taggables.taggable_type', Rotation::class);
                })
                ->where('rotations.user_id', $user->id)
                ->distinct()
                ->count('taggables.vibetag_id'),
        );
    }
}
