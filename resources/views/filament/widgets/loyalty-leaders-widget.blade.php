<x-filament-widgets::widget>
    <x-filament::section heading="Loyalty Leaders" icon="heroicon-o-trophy">
        @php
            $topCustomers = $this->getTopCustomers();
            $totalPoints = $this->getTotalPointsOutstanding();
            $recentAwards = $this->getRecentAwards();
        @endphp

        <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center; margin-bottom: 16px;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #e8b04a;">{{ number_format($totalPoints) }}</div>
            <div style="font-size: 0.75rem; color: #6b4c3b;">Total Points Outstanding</div>
        </div>

        @if(count($topCustomers) > 0)
            <div style="font-size: 0.75rem; color: #6b4c3b; margin-bottom: 8px; font-weight: 600;">Top 5 Members</div>
            @foreach($topCustomers as $i => $customer)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: {{ $i === 0 ? '#e8b04a15' : '#fdf8f2' }}; border-radius: 6px; margin-bottom: 6px; font-size: 0.8rem; {{ $i === 0 ? 'border: 1px solid #e8b04a40;' : '' }}">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 700; color: {{ $i === 0 ? '#e8b04a' : '#8b6844' }}; width: 18px;">{{ $i + 1 }}.</span>
                        <span style="font-weight: 600; color: #3d2314;">{{ $customer['name'] }}</span>
                    </div>
                    <span style="font-weight: 700; color: #6b4c3b;">{{ number_format($customer['points']) }} pts</span>
                </div>
            @endforeach
        @else
            <div style="color: #8b6844; font-style: italic; font-size: 0.8rem; text-align: center; padding: 12px;">No loyalty points awarded yet</div>
        @endif

        @if(count($recentAwards) > 0)
            <div style="font-size: 0.75rem; color: #6b4c3b; margin-top: 16px; margin-bottom: 8px; font-weight: 600;">Recent Awards</div>
            @foreach($recentAwards as $award)
                <div style="font-size: 0.75rem; color: #6b4c3b; padding: 4px 0; border-bottom: 1px solid #fdf8f2;">
                    <span style="font-weight: 600; color: #3d2314;">{{ $award['customer'] }}</span>
                    +{{ $award['points'] }} pts
                    <span style="color: #8b6844;">{{ $award['date'] }}</span>
                </div>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
