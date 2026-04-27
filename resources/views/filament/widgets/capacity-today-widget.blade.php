<x-filament-widgets::widget>
    <x-filament::section heading="Order Capacity" icon="heroicon-o-chart-bar">
        @php
            // sm: today only — drop tomorrow + blocked days warning to keep
            // the tile glanceable. md: full picture.
            $days = $this->isSize('sm')
                ? [['label' => 'Today', 'data' => $this->getTodayCapacity()]]
                : [['label' => 'Today', 'data' => $this->getTodayCapacity()], ['label' => 'Tomorrow', 'data' => $this->getTomorrowCapacity()]];

            $blocked = $this->isSize('sm') ? [] : $this->getBlockedDaysWarning();
        @endphp

        @foreach ($days as $day)
            @php
                $pct = $day['data']['percentage'];
                $barBg = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#e8b04a' : '#d4a574');
            @endphp
            <div class="mb-4">
                <div class="flex justify-between mb-1.5 text-[0.8rem]">
                    <span class="font-semibold text-brand-900">{{ $day['label'] }}</span>
                    <span class="text-brand-700">{{ $day['data']['current'] }} / {{ $day['data']['max'] }} orders ({{ $pct }}%)</span>
                </div>
                <div class="bg-brand-50 rounded-full h-3 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%; background: {{ $barBg }};"></div>
                </div>
            </div>
        @endforeach

        @if (count($blocked) > 0)
            <div class="px-3 py-2.5 bg-red-600/10 border border-red-600/30 rounded-lg mt-1">
                <div class="text-xs font-semibold text-red-600 mb-1.5">Blocked Days This Week</div>
                @foreach ($blocked as $b)
                    <div class="text-[0.8rem] text-brand-700">
                        <span class="font-semibold">{{ $b['date'] }}</span> — {{ $b['reason'] }}
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
