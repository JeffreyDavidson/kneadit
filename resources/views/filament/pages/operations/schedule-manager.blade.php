<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-content p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Weekly Overview</h3>
                <div class="mt-3 grid grid-cols-7 gap-2">
                    @foreach (\App\Enums\Staff\DayOfWeek::phpWeekOrder() as $num => $dayEnum)
                        @php
                            $day = $this->schedule[$num] ?? null;
                            $isOpen = $day['is_open'] ?? false;
                        @endphp
                        <div class="rounded-lg p-3 text-center {{ $isOpen ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700' }}">
                            <div class="font-medium text-sm {{ $isOpen ? 'text-green-700 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ substr($dayEnum->getLabel(), 0, 3) }}
                            </div>
                            @if ($isOpen)
                                <div class="text-xs text-green-600 dark:text-green-500 mt-1">
                                    {{ $day['open_time'] ? \Carbon\Carbon::parse($day['open_time'])->format('g:ia') : '' }}
                                    -
                                    {{ $day['close_time'] ? \Carbon\Carbon::parse($day['close_time'])->format('g:ia') : '' }}
                                </div>
                            @else
                                <div class="text-xs text-gray-400 mt-1">Closed</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
