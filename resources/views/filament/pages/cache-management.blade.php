<x-filament-panels::page>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        @foreach ([
            'App Environment'  => $this->stats['app_env'] ?? '—',
            'Cache Driver'     => $this->stats['cache_driver'] ?? '—',
            'Queue Driver'     => $this->stats['queue_driver'] ?? '—',
            'Pending Jobs'     => $this->stats['pending_jobs'] ?? '—',
            'Failed Jobs'      => $this->stats['failed_jobs'] ?? '—',
            'Covers Cached'    => $this->stats['covers_cached'] ?? '—',
        ] as $label => $value)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $value }}</p>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
