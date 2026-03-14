<x-filament-widgets::widget>
    <x-filament::section heading="Order Capacity" icon="heroicon-o-chart-bar">
        @php
            $today = $this->getTodayCapacity();
            $tomorrow = $this->getTomorrowCapacity();
            $blocked = $this->getBlockedDaysWarning();
        @endphp

        @foreach([['label' => 'Today', 'data' => $today], ['label' => 'Tomorrow', 'data' => $tomorrow]] as $day)
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.8rem;">
                    <span style="font-weight: 600; color: #3d2314;">{{ $day['label'] }}</span>
                    <span style="color: #6b4c3b;">{{ $day['data']['current'] }} / {{ $day['data']['max'] }} orders ({{ $day['data']['percentage'] }}%)</span>
                </div>
                <div style="background: #fdf8f2; border-radius: 999px; height: 12px; overflow: hidden;">
                    <div style="
                        height: 100%;
                        border-radius: 999px;
                        width: {{ $day['data']['percentage'] }}%;
                        background: {{ $day['data']['percentage'] >= 90 ? '#dc2626' : ($day['data']['percentage'] >= 70 ? '#e8b04a' : '#d4a574') }};
                        transition: width 0.3s ease;
                    "></div>
                </div>
            </div>
        @endforeach

        @if(count($blocked) > 0)
            <div style="padding: 10px 12px; background: #dc262610; border: 1px solid #dc262630; border-radius: 8px; margin-top: 4px;">
                <div style="font-size: 0.75rem; font-weight: 600; color: #dc2626; margin-bottom: 6px;">Blocked Days This Week</div>
                @foreach($blocked as $b)
                    <div style="font-size: 0.8rem; color: #6b4c3b;">
                        <span style="font-weight: 600;">{{ $b['date'] }}</span> — {{ $b['reason'] }}
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
