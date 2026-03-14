<x-filament-widgets::widget>
    <x-filament::section heading="Gift Cards" icon="heroicon-o-gift">
        @php
            $balance = $this->getTotalOutstandingBalance();
            $activeCards = $this->getActiveCardsCount();
            $recent = $this->getRecentlyRedeemed();
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">${{ number_format($balance, 2) }}</div>
                <div style="font-size: 0.75rem; color: #6b4c3b;">Outstanding Balance</div>
            </div>
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $activeCards }}</div>
                <div style="font-size: 0.75rem; color: #6b4c3b;">Active Cards</div>
            </div>
        </div>

        <div style="margin-top: 16px;">
            <div style="font-size: 0.75rem; color: #6b4c3b; margin-bottom: 8px; font-weight: 600;">Recent Redemptions</div>
            @if(count($recent) > 0)
                @foreach($recent as $txn)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #fdf8f2; border-radius: 6px; margin-bottom: 6px; font-size: 0.8rem;">
                        <div>
                            <span style="font-weight: 600; color: #3d2314;">{{ $txn['code'] }}</span>
                            <span style="color: #8b6844; margin-left: 6px;">{{ $txn['date'] }}</span>
                        </div>
                        <div style="font-weight: 600; color: #6b4c3b;">${{ number_format(abs($txn['amount']), 2) }}</div>
                    </div>
                @endforeach
            @else
                <div style="color: #8b6844; font-style: italic; font-size: 0.8rem; padding: 8px 12px; background: #fdf8f2; border-radius: 6px;">No recent redemptions</div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
