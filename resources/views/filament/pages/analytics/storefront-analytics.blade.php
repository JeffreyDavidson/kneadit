<x-filament-panels::page>
    <div>
        {{-- Time Filter --}}
        <div class="mb-6 flex gap-2">
            @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'all' => 'All Time'] as $key => $label)
                <button
                    wire:click="setPeriod('{{ $key }}')"
                    @class([
                        'px-4 py-2 rounded-lg border-2 cursor-pointer text-sm transition-all',
                        'border-amber-700 bg-amber-100 text-amber-800 font-bold' => $this->period === $key,
                        'border-gray-200 bg-white text-gray-500 font-medium' => $this->period !== $key,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Overview Cards --}}
        <div class="mb-8 grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-4">
            @foreach ([
                ['label' => 'Total Views', 'value' => number_format($this->getTotalViews()), 'gradient' => 'from-amber-100 to-amber-200', 'border' => 'border-amber-500', 'labelColor' => 'text-amber-800', 'valueColor' => 'text-amber-900'],
                ['label' => 'Unique Visitors', 'value' => number_format($this->getUniqueVisitors()), 'gradient' => 'from-pink-100 to-pink-200', 'border' => 'border-pink-500', 'labelColor' => 'text-pink-800', 'valueColor' => 'text-pink-900'],
                ['label' => 'Most Popular Page', 'value' => $this->getMostPopularPage(), 'gradient' => 'from-blue-100 to-blue-200', 'border' => 'border-blue-500', 'labelColor' => 'text-blue-800', 'valueColor' => 'text-blue-900', 'extra' => 'capitalize'],
                ['label' => 'Conversion Rate', 'value' => $this->getConversionRate() . '%', 'gradient' => 'from-emerald-100 to-emerald-200', 'border' => 'border-emerald-500', 'labelColor' => 'text-emerald-800', 'valueColor' => 'text-emerald-900'],
            ] as $card)
                <div class="bg-gradient-to-br {{ $card['gradient'] }} rounded-xl p-5 border {{ $card['border'] }}">
                    <div class="text-[13px] {{ $card['labelColor'] }} font-semibold uppercase tracking-wider">
                        {{ $card['label'] }}
                    </div>
                    <div class="text-[32px] font-extrabold {{ $card['valueColor'] }} mt-1 {{ $card['extra'] ?? '' }}">
                        {{ $card['value'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-8 grid grid-cols-2 gap-6">
            {{-- Page Views Chart --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="m-0 mb-4 flex items-center gap-2 text-base font-bold text-gray-800">
                    <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-5 w-5" />
                    Views by Page
                </h3>
                @php
                    $pageViews = $this->getPageViewsChart();
                    $maxPageViews = $pageViews->max('views') ?: 1;
                @endphp
                @forelse ($pageViews as $pv)
                    <div class="mb-2 flex items-center gap-3">
                        <div class="w-[70px] text-[13px] font-semibold text-gray-600 capitalize">{{ $pv->page }}</div>
                        <div class="h-7 flex-1 overflow-hidden rounded-md bg-gray-100">
                            <div
                                class="flex h-full min-w-[30px] items-center rounded-md bg-gradient-to-r from-amber-500 to-amber-600 pl-2"
                                style="width: {{ ($pv->views / $maxPageViews) * 100 }}%;"
                            >
                                <span class="text-xs font-bold text-white">{{ number_format($pv->views) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No page views recorded yet.</p>
                @endforelse
            </div>

            {{-- Conversion Funnel --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="m-0 mb-4 flex items-center gap-2 text-base font-bold text-gray-800">
                    <x-filament::icon icon="heroicon-o-funnel" class="h-5 w-5" />
                    Conversion Funnel
                </h3>
                @php
                    $funnel = $this->getConversionFunnel();
                    $stepGradients = ['from-amber-500 to-amber-600', 'from-orange-500 to-orange-600', 'from-red-500 to-red-600', 'from-emerald-500 to-emerald-600'];
                @endphp
                @foreach ($funnel as $i => $step)
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">{{ $step['label'] }}</span>
                            <span class="text-sm font-bold text-gray-800">{{ number_format($step['count']) }}</span>
                        </div>
                        <div class="h-6 overflow-hidden rounded-md bg-gray-100">
                            <div
                                class="h-full rounded-md bg-gradient-to-r {{ $stepGradients[$i] }}"
                                style="width: {{ $step['percentage'] }}%; min-width: {{ $step['count'] > 0 ? '4px' : '0' }};"
                            ></div>
                        </div>
                        @if ($step['dropoff'] !== null)
                            <div class="mt-0.5 text-[11px] text-red-500">↓ {{ $step['dropoff'] }}% drop-off</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Daily Trend --}}
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="m-0 mb-4 flex items-center gap-2 text-base font-bold text-gray-800">
                <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5" />
                Daily Views (Last 30 Days)
            </h3>
            @php
                $daily = $this->getDailyTrend();
                $maxDaily = $daily->max('views') ?: 1;
            @endphp
            <div class="flex h-40 items-end gap-[3px]">
                @forelse ($daily as $day)
                    <div
                        class="flex h-full flex-1 flex-col items-center justify-end"
                        title="{{ $day->date }}: {{ $day->views }} views"
                    >
                        <div
                            class="w-full rounded-t-[3px] bg-gradient-to-t from-amber-500 to-amber-400"
                            style="height: {{ max(($day->views / $maxDaily) * 100, 2) }}%;"
                        ></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No data yet.</p>
                @endforelse
            </div>
            @if ($daily->isNotEmpty())
                <div class="mt-1.5 flex justify-between">
                    <span class="text-[11px] text-gray-400">{{ $daily->first()?->date }}</span>
                    <span class="text-[11px] text-gray-400">{{ $daily->last()?->date }}</span>
                </div>
            @endif
        </div>

        {{-- Top Products --}}
        @php $topProducts = $this->getTopProducts(); @endphp
        @if ($topProducts->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="m-0 mb-4 flex items-center gap-2 text-base font-bold text-gray-800">
                    <x-filament::icon icon="heroicon-o-cake" class="h-5 w-5" />
                    Top Products Viewed
                </h3>
                @php $maxProduct = $topProducts->max('views') ?: 1; @endphp
                @foreach ($topProducts as $product)
                    <div class="mb-2 flex items-center gap-3">
                        <div class="w-[140px] truncate text-[13px] font-semibold text-gray-600">
                            {{ $product->name }}
                        </div>
                        <div class="h-6 flex-1 overflow-hidden rounded-md bg-gray-100">
                            <div
                                class="flex h-full min-w-[30px] items-center rounded-md bg-gradient-to-r from-pink-500 to-pink-700 pl-2"
                                style="width: {{ ($product->views / $maxProduct) * 100 }}%;"
                            >
                                <span class="text-[11px] font-bold text-white">{{ number_format($product->views) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
