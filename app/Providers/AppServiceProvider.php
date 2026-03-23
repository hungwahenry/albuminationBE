<?php

namespace App\Providers;

use App\Services\MusicBrainz\MusicBrainzClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
}
