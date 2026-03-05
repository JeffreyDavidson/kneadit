<x-filament-panels::page>
    <style>
        .trend-up { color: var(--status-success); font-weight: 700; }
        .trend-down { color: var(--status-danger); font-weight: 700; }
        .trend-flat { color: var(--brand-600); font-weight: 600; }
    </style>

    <div style="max-width: 1200px; margin: 0 auto;">
        <x-admin.nav-controls :label="$this->monthLabel" prevClick="previousMonth" nextClick="nextMonth" prevLabel="← Previous" nextLabel="Next →" />

        @forelse ($this->trendsData as $group)
            <x-admin.card :title="$group['category']">
                <x-admin.data-table data-admin-table>
                    <x-slot:head>
                        <th style="text-align:left;">Product</th>
                        <th style="text-align:right;">{{ $this->prevMonthLabel }}</th>
                        <th style="text-align:right;">{{ $this->monthLabel }}</th>
                        <th style="text-align:right;">Change</th>
                        <th style="text-align:right;">Trend</th>
                    </x-slot:head>
                    @foreach ($group['products'] as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td style="text-align:right;">{{ $product['previous'] }}</td>
                            <td style="text-align:right;">{{ $product['current'] }}</td>
                            <td style="text-align:right;">
                                <span class="trend-{{ $product['trend'] }}">{{ $product['change'] > 0 ? '+' : '' }}{{ $product['change'] }}%</span>
                            </td>
                            <td style="text-align:right;">
                                <span class="trend-{{ $product['trend'] }}" style="font-size:1rem;">{{ $product['trend'] === 'up' ? '↑' : ($product['trend'] === 'down' ? '↓' : '→') }}</span>
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
