<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-(--kn-surface) shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="fi-section-content p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-(--kn-ink)">Weekly Overview</h3>
                <div class="mt-3 grid grid-cols-7 gap-2">
                    @foreach (\App\Enums\Staff\DayOfWeek::phpWeekOrder() as $num => $dayEnum)
                        @php
                            $day = $this->schedule[$num] ?? null;
                            $isOpen = $day['is_open'] ?? false;
                        @endphp
                        <div class="rounded-lg p-3 text-center {{ $isOpen ? 'bg-(--kn-success-tint) dark:bg-green-900/20 border border-(--kn-success) dark:border-green-800' : 'bg-(--kn-surface-sunken) border border-(--kn-border)' }}">
                            <div class="font-medium text-sm {{ $isOpen ? 'text-(--kn-success) dark:text-green-400' : 'text-(--kn-muted)' }}">
                                {{ substr($dayEnum->getLabel(), 0, 3) }}
                            </div>
                            @if ($isOpen)
                                <div class="mt-1 text-xs text-(--kn-success) dark:text-green-500">
                                    {{ $day['open_time'] ? \Carbon\Carbon::createFromFormat('H:i', $day['open_time'])->format('g:ia') : '' }} - {{ $day['close_time'] ? \Carbon\Carbon::createFromFormat('H:i', $day['close_time'])->format('g:ia') : '' }}
                                </div>
                            @else
                                <div class="mt-1 text-xs text-(--kn-muted)">Closed</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
