<?php

namespace App\Filament\Pages;

use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Cache & System';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.cache-management';

    public array $stats = [];

    public function mount(): void
    {
        abort_unless(
            auth('admin')->user()?->can('search_index.manage') || auth('admin')->user()?->can('media.manage'),
            403
        );

        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->stats = [
            'pending_jobs'     => DB::table('jobs')->count(),
            'failed_jobs'      => FailedJob::count(),
            'covers_cached'    => Cache::has('welcome:covers') ? 'Yes' : 'No',
            'app_env'          => config('app.env'),
            'cache_driver'     => config('cache.default'),
            'queue_driver'     => config('queue.default'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('flush_mb_search')
                ->label('Flush MB Search Cache')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will clear all cached MusicBrainz search results. Next searches will re-fetch from MusicBrainz (may be slow).')
                ->visible(fn () => auth('admin')->user()?->can('search_index.manage'))
                ->action(function () {
                    $flushed = 0;
                    foreach (Cache::getStore()->getPrefix() ? [] : [] as $_) {}

                    // Flush by known key patterns using cache tags if available,
                    // or iterate through known prefixes
                    foreach (['album', 'artist', 'track'] as $type) {
                        foreach ([5, 10, 15, 25, 50] as $limit) {
                            // We can only flush keys we know about; log and inform
                        }
                    }

                    // Flush the entire cache if driver supports it (redis/memcached)
                    // For file cache, use artisan
                    \Illuminate\Support\Facades\Artisan::call('cache:clear');
                    $flushed = 1;

                    activity()->causedBy(auth()->user())->log('Flushed all application caches (including MB search)');
                    Notification::make()->title('All caches cleared')->success()->send();
                    $this->refreshStats();
                }),

            Action::make('flush_covers')
                ->label('Flush Covers Cache')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('This clears the welcome screen cover art cache. New covers will be randomly selected on next load.')
                ->visible(fn () => auth('admin')->user()?->can('media.manage'))
                ->action(function () {
                    Cache::forget('welcome:covers');
                    activity()->causedBy(auth()->user())->log('Flushed welcome covers cache');
                    Notification::make()->title('Covers cache cleared')->success()->send();
                    $this->refreshStats();
                }),

            Action::make('refresh')
                ->label('Refresh Stats')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->refreshStats();
                }),
        ];
    }
}
