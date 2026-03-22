<x-filament-panels::page>
    @if (empty($this->tasks))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">No scheduled tasks are registered.</p>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">Registered Tasks ({{ count($this->tasks) }})</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="py-2 pr-4">Command</th>
                            <th class="py-2 pr-4">Expression</th>
                            <th class="py-2 pr-4">Timezone</th>
                            <th class="py-2 pr-4">Next Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->tasks as $task)
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs text-gray-900 dark:text-white">
                                    {{ $task['command'] }}
                                    @if ($task['description'])
                                        <p class="text-gray-400 font-sans">{{ $task['description'] }}</p>
                                    @endif
                                </td>
                                <td class="py-2 pr-4 font-mono text-xs">{{ $task['expression'] }}</td>
                                <td class="py-2 pr-4 text-gray-500">{{ $task['timezone'] }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $task['next_due'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
