<x-filament-widgets::widget>
    <x-filament::section heading="Customer Insights" icon="heroicon-o-user-group">
        @php $avg = $this->getAvgOrderValue(); @endphp
        <div class="flex flex-col gap-4">
            @php
                $rows = [
                    ['label' => 'New This Week', 'value' => $this->getNewCustomersThisWeek(), 'icon' => 'heroicon-s-user-plus', 'gradient' => 'from-brand-50 to-brand-100', 'iconBg' => 'bg-brand-700', 'labelColor' => 'text-brand-700', 'extra' => null],
                    ['label' => 'Repeat Rate', 'value' => $this->getRepeatCustomerRate().'%', 'icon' => 'heroicon-s-arrow-path-rounded-square', 'gradient' => 'from-brand-100 to-brand-200', 'iconBg' => 'bg-brand-300', 'labelColor' => 'text-amber-800', 'extra' => null],
                    ['label' => 'Avg Order Value', 'value' => null, 'icon' => 'heroicon-s-currency-dollar', 'gradient' => 'from-brand-150 to-brand-100', 'iconBg' => 'bg-brand-600', 'labelColor' => 'text-brand-600', 'extra' => 'aov'],
                ];
            @endphp
            @foreach ($rows as $row)
                <div class="flex justify-between items-center px-4 py-3.5 rounded-xl bg-gradient-to-br {{ $row['gradient'] }}">
                    <div>
                        <div class="text-[0.78rem] uppercase font-semibold tracking-wider {{ $row['labelColor'] }}">{{ $row['label'] }}</div>
                        <div class="text-2xl font-bold text-brand-900">
                            @if ($row['extra'] === 'aov')
                                @money($avg['value'])
                                <span class="text-[0.85rem] {{ $avg['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $avg['trend'] === 'up' ? '↑' : '↓' }}
                                </span>
                            @else
                                {{ $row['value'] }}
                            @endif
                        </div>
                    </div>
                    <div class="text-white w-10 h-10 rounded-xl flex items-center justify-center {{ $row['iconBg'] }}">
                        <x-dynamic-component :component="$row['icon']" class="w-5 h-5" />
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
