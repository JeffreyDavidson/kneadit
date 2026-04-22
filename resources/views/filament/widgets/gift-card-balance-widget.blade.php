<x-filament-widgets::widget>
    <x-filament::section heading="Gift Cards" icon="heroicon-o-gift">
        @php
            $balance = $this->getTotalOutstandingBalance();
            $activeCards = $this->getActiveCardsCount();
            $recent = $this->getRecentlyRedeemed();
        @endphp

        <div class="grid grid-cols-2 gap-4">
            <x-admin.stat-cell label="Outstanding Balance">@money($balance)</x-admin.stat-cell>
            <x-admin.stat-cell label="Active Cards">{{ $activeCards }}</x-admin.stat-cell>
        </div>

        <div class="mt-4">
            <div class="text-xs text-brand-700 mb-2 font-semibold">Recent Redemptions</div>
            @if (count($recent) > 0)
                @foreach ($recent as $txn)
                    <div class="flex justify-between items-center px-3 py-2 bg-brand-50 rounded-md mb-1.5 text-[0.8rem]">
                        <div>
                            <span class="font-semibold text-brand-900">{{ $txn['code'] }}</span>
                            <span class="text-brand-600 ml-1.5">{{ $txn['date'] }}</span>
                        </div>
                        <div class="font-semibold text-brand-700">@money(abs($txn['amount']))</div>
                    </div>
                @endforeach
            @else
                <div class="text-brand-600 italic text-[0.8rem] px-3 py-2 bg-brand-50 rounded-md">No recent redemptions</div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
