<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div class="flex gap-2 mb-6">
        @foreach ([
            'compare' => 'Compare',
            'leaderboard' => 'Leaderboard',
        ] as $key => $label)
            <button wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-honey/25 cursor-pointer',
                    'bg-honey text-warm-black' => $activeTab === $key,
                    'bg-transparent text-honey' => $activeTab !== $key,
                ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Compare Tab --}}
    @if ($activeTab === 'compare')
        @php
            $allTenants = $this->getAllTenants();
            $comparisonData = $this->getComparisonData();
        @endphp

        <div x-data="{
            tenant1: '{{ $this->selectedTenants[0] ?? '' }}',
            tenant2: '{{ $this->selectedTenants[1] ?? '' }}',
            tenant3: '{{ $this->selectedTenants[2] ?? '' }}',
            compare() {
                const params = new URLSearchParams();
                if (this.tenant1) params.append('tenants[]', this.tenant1);
                if (this.tenant2) params.append('tenants[]', this.tenant2);
                if (this.tenant3) params.append('tenants[]', this.tenant3);
                window.location.search = params.toString();
            }
        }">
            <x-central.card title="Select Tenants to Compare" class="mb-6">
                <div class="flex gap-4 flex-wrap items-end">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="flex-1 min-w-[200px]">
                        <x-central.eyebrow as="label" class="block mb-1">Tenant {{ $i }}</x-central.eyebrow>
                        <x-central.select x-model="tenant{{ $i }}">
                            <option value="">— Select —</option>
                            @foreach ($allTenants as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-central.select>
                    </div>
                @endfor
                <x-central.button @click="compare()" class="h-[38px]">Compare</x-central.button>
                </div>
            </x-central.card>
        </div>

        @if (count($comparisonData) > 0)
            @php
                $gridCols = match (count($comparisonData)) {
                    1 => 'grid-cols-1',
                    2 => 'grid-cols-2',
                    default => 'grid-cols-3',
                };
            @endphp
            <div class="grid gap-4 mb-6 {{ $gridCols }}">
                @foreach ($comparisonData as $tenant)
                    @php
                        $planColor = match ($tenant['plan']) {
                            'premium' => 'honey',
                            'pro' => 'golden',
                            default => 'honey-soft',
                        };
                    @endphp
                    <x-central.card class="flex flex-col">
                        <div class="mb-4 text-center pb-4 border-b border-honey/8">
                            <div class="text-white text-base font-bold mb-2">{{ $tenant['name'] }}</div>
                            <x-central.badge :color="$planColor">{{ $tenant['plan'] }}</x-central.badge>
                        </div>

                        <div class="flex flex-col gap-2 flex-1">
                            @php
                                $rows = [
                                    ['label' => 'Total Orders', 'value' => $tenant['total_orders']],
                                    ['label' => 'This Month', 'value' => $tenant['month_orders']],
                                    ['label' => 'Products', 'value' => $tenant['total_products']],
                                    ['label' => 'Categories', 'value' => $tenant['total_categories']],
                                    ['label' => 'Avg Review', 'value' => ($tenant['avg_review'] ?: '—') . '/5'],
                                    ['label' => 'Setup', 'value' => $tenant['setup_completed'] . '/7'],
                                    ['label' => 'Days Since Signup', 'value' => $tenant['days_since_signup']],
                                ];
                            @endphp
                            @foreach ($rows as $row)
                                <x-central.metric-row :label="$row['label']">{{ $row['value'] }}</x-central.metric-row>
                            @endforeach
                            @php
                                $healthColor = $tenant['health_score'] > 70 ? 'text-emerald-500' : ($tenant['health_score'] >= 40 ? 'text-amber-500' : 'text-red-500');
                            @endphp
                            <x-central.metric-row label="Health Score" :value-class="$healthColor.' font-bold'">{{ $tenant['health_score'] }}/100</x-central.metric-row>
                        </div>
                    </x-central.card>
                @endforeach
            </div>

            <x-central.card title="Visual Comparison">
                @php
                    $chartMetrics = ['total_orders', 'total_products', 'health_score'];
                    $chartLabels = ['Total Orders', 'Total Products', 'Health Score'];
                    $barClasses = ['bg-honey', 'bg-golden', 'bg-butter'];
                @endphp
                @foreach ($chartMetrics as $idx => $metric)
                    @php $maxVal = max(array_column($comparisonData, $metric)) ?: 1; @endphp
                    <div class="mb-6">
                        <x-central.eyebrow class="mb-2">{{ $chartLabels[$idx] }}</x-central.eyebrow>
                        @foreach ($comparisonData as $tIdx => $tenant)
                            @php $pct = round(($tenant[$metric] / $maxVal) * 100); @endphp
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="text-parchment text-xs w-[120px] text-right truncate">{{ $tenant['name'] }}</span>
                                <div class="flex-1 bg-espresso rounded h-2 overflow-hidden">
                                    <div class="h-full rounded transition-all duration-300 {{ $barClasses[$tIdx % 3] }}" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="text-parchment text-[0.8rem] font-bold w-[50px]">{{ $tenant[$metric] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </x-central.card>
        @else
            <x-central.card padding="p-12" class="text-center">
                <div class="mb-4">
                    <x-heroicon-o-chart-bar class="w-12 h-12 inline-block text-honey" />
                </div>
                <div class="text-parchment text-base">Select 2–3 tenants above to compare their metrics side by side.</div>
            </x-central.card>
        @endif
    @endif

    {{-- Leaderboard Tab --}}
    @if ($activeTab === 'leaderboard')
        @php
            $leaderboard = $this->getLeaderboardData();
            $summary = $this->getLeaderboardSummaryStats();
            $top3 = array_slice($leaderboard, 0, 3);
            $podiumHeights = [140, 110, 90];
        @endphp

        <div class="grid grid-cols-3 gap-4 mb-6">
            <x-central.stat-card label="Total Platform Orders" value-class="text-[1.75rem] text-white">{{ number_format($summary['total_orders']) }}</x-central.stat-card>
            <x-central.stat-card label="Average Orders / Bakery" value-class="text-[1.75rem] text-white">{{ $summary['avg_orders'] }}</x-central.stat-card>
            <x-central.stat-card label="Total Bakeries" value-class="text-[1.75rem] text-white">{{ $summary['total_bakeries'] }}</x-central.stat-card>
        </div>

        @if (count($top3) >= 3)
            <x-central.card class="mb-6">
                <div class="text-white font-bold text-base text-center mb-6">
                    <x-heroicon-s-star class="w-5 h-5 inline-block align-middle mr-1 text-honey" />
                    Top 3 Bakeries
                </div>
                @php
                    $podium = [
                        ['rank' => 2, 'tenant' => $top3[1], 'width' => 'w-40', 'gradient' => 'from-slate-400 to-slate-500', 'nameColor' => 'text-parchment', 'ordersColor' => 'text-cinnamon', 'fontSize' => 'text-[0.875rem]'],
                        ['rank' => 1, 'tenant' => $top3[0], 'width' => 'w-[180px]', 'gradient' => 'from-golden to-honey', 'nameColor' => 'text-white', 'ordersColor' => 'text-honey', 'fontSize' => 'text-base'],
                        ['rank' => 3, 'tenant' => $top3[2], 'width' => 'w-40', 'gradient' => 'from-amber-700 to-amber-800', 'nameColor' => 'text-parchment', 'ordersColor' => 'text-cinnamon', 'fontSize' => 'text-[0.875rem]'],
                    ];
                @endphp
                <div class="flex items-end justify-center gap-4 pt-4">
                    @foreach ($podium as $idx => $entry)
                        <div class="text-center {{ $entry['width'] }}">
                            <div class="font-bold mb-2 {{ $entry['nameColor'] }} {{ $entry['fontSize'] }}">{{ $entry['tenant']['name'] }}</div>
                            <div class="text-xs mb-2 {{ $entry['ordersColor'] }}">{{ $entry['tenant']['total_orders'] }} orders</div>
                            <div class="rounded-t-lg flex items-center justify-center bg-gradient-to-b {{ $entry['gradient'] }}" style="height: {{ $podiumHeights[match($entry['rank']) { 1 => 0, 2 => 1, 3 => 2 }] }}px;">
                                <span class="text-white text-[1.75rem] font-bold">#{{ $entry['rank'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-central.card>
        @endif

        <x-central.card padding="p-0" class="overflow-hidden">
            <div class="px-6 py-6 border-b border-honey/8">
                <div class="text-white font-bold text-base">Full Rankings</div>
            </div>
            <x-central.table>
                <thead>
                    <x-central.tr>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-left">Rank</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-left">Bakery</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-left">Owner</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-center">Plan</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Total Orders</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">This Month</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Products</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Avg Review</x-central.eyebrow>
                    </x-central.tr>
                </thead>
                <tbody>
                    @foreach ($leaderboard as $idx => $tenant)
                        @php
                            $rank = $idx + 1;
                            $isTop3 = $rank <= 3;
                            $rankClass = match ($rank) {
                                1 => 'text-honey',
                                2 => 'text-slate-400',
                                3 => 'text-amber-700',
                                default => 'text-parchment',
                            };
                            $planColor = match ($tenant['plan']) {
                                'premium' => 'honey',
                                'pro' => 'golden',
                                default => 'honey-soft',
                            };
                        @endphp
                        <x-central.tr :highlight="$isTop3">
                            <x-central.td>
                                <span class="font-bold {{ $rankClass }} {{ $isTop3 ? 'text-[1.1rem]' : 'text-sm' }}">#{{ $rank }}</span>
                            </x-central.td>
                            <x-central.td tone="white" class="font-bold">{{ $tenant['name'] }}</x-central.td>
                            <x-central.td class="text-[0.8rem]">{{ $tenant['owner'] }}</x-central.td>
                            <x-central.td align="center">
                                <x-central.badge :color="$planColor">{{ $tenant['plan'] }}</x-central.badge>
                            </x-central.td>
                            <x-central.td align="right" tone="white" class="font-bold">{{ $tenant['total_orders'] }}</x-central.td>
                            <x-central.td align="right">{{ $tenant['month_orders'] }}</x-central.td>
                            <x-central.td align="right">{{ $tenant['total_products'] }}</x-central.td>
                            <x-central.td align="right">{{ $tenant['avg_review'] ?: '—' }}</x-central.td>
                        </x-central.tr>
                    @endforeach
                </tbody>
            </x-central.table>
        </x-central.card>
    @endif
</x-filament-panels::page>
