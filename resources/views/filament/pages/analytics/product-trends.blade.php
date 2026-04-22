<x-filament-panels::page>
    @php
        $trendClass = fn (string $trend) => match ($trend) {
            'up' => 'text-emerald-600 font-bold',
            'down' => 'text-red-600 font-bold',
            default => 'text-brand-600 font-semibold',
        };
        $trendArrow = fn (string $trend) => match ($trend) {
            'up' => '↑',
            'down' => '↓',
            default => '→',
        };
    @endphp

    <div class="max-w-[1200px] mx-auto">
        <x-admin.nav-controls :label="$this->monthLabel" prevClick="previousMonth" nextClick="nextMonth" prevLabel="← Previous" nextLabel="Next →" />

        @forelse ($this->trendsData as $group)
            <x-admin.card :title="$group['category']">
                <x-admin.data-table data-admin-table>
                    <x-slot:head>
                        <th class="text-left">Product</th>
                        <th class="text-right">{{ $this->prevMonthLabel }}</th>
                        <th class="text-right">{{ $this->monthLabel }}</th>
                        <th class="text-right">Change</th>
                        <th class="text-right">Trend</th>
                    </x-slot:head>
                    @foreach ($group['products'] as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td class="text-right">{{ $product['previous'] }}</td>
                            <td class="text-right">{{ $product['current'] }}</td>
                            <td class="text-right">
                                <span class="{{ $trendClass($product['trend']) }}">{{ $product['change'] > 0 ? '+' : '' }}{{ $product['change'] }}%</span>
                            </td>
                            <td class="text-right">
                                <span class="text-base {{ $trendClass($product['trend']) }}">{{ $trendArrow($product['trend']) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </x-admin.data-table>
            </x-admin.card>
        @empty
            <x-admin.empty-state icon="📊" title="No order data found for this period" subtitle="Try navigating to a different month." />
        @endforelse
    </div>
</x-filament-panels::page>
