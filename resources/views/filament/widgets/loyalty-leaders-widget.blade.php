<x-filament-widgets::widget>
    <x-filament::section heading="Loyalty Leaders" icon="heroicon-o-trophy">
        @php
            $topCustomers = $this->getTopCustomers();
            $totalPoints = $this->getTotalPointsOutstanding();
            $recentAwards = $this->getRecentAwards();
        @endphp

        <x-admin.stat-cell label="Total Points Outstanding" value-class="text-2xl font-bold text-[#e8b04a]" class="mb-4">
            {{ number_format($totalPoints) }}
        </x-admin.stat-cell>

        @if (count($topCustomers) > 0)
            <div class="text-xs text-brand-700 mb-2 font-semibold">Top 5 Members</div>
            @foreach ($topCustomers as $i => $customer)
                <div @class([
                    'flex justify-between items-center px-3 py-2 rounded-md mb-1.5 text-[0.8rem]',
                    'bg-[#e8b04a]/15 border border-[#e8b04a]/40' => $i === 0,
                    'bg-brand-50' => $i !== 0,
                ])>
                    <div class="flex items-center gap-2">
                        <span class="font-bold w-[18px] {{ $i === 0 ? 'text-[#e8b04a]' : 'text-brand-600' }}">{{ $i + 1 }}.</span>
                        <span class="font-semibold text-brand-900">{{ $customer['name'] }}</span>
                    </div>
                    <span class="font-bold text-brand-700">{{ number_format($customer['points']) }} pts</span>
                </div>
            @endforeach
        @else
            <div class="text-brand-600 italic text-[0.8rem] text-center p-3">No loyalty points awarded yet</div>
        @endif

        @if (count($recentAwards) > 0)
            <div class="text-xs text-brand-700 mt-4 mb-2 font-semibold">Recent Awards</div>
            @foreach ($recentAwards as $award)
                <div class="text-xs text-brand-700 py-1 border-b border-brand-50">
                    <span class="font-semibold text-brand-900">{{ $award['customer'] }}</span>
                    +{{ $award['points'] }} pts
                    <span class="text-brand-600">{{ $award['date'] }}</span>
                </div>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
