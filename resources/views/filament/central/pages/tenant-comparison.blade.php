<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        @foreach ([
            'compare' => 'Compare',
            'leaderboard' => 'Leaderboard',
        ] as $key => $label)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                style="padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(212,146,12,0.25); cursor: pointer;
                    {{ $activeTab === $key ? 'background: #d4920c; color: #1c1410;' : 'background: transparent; color: #d4920c;' }}"
            >
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
                    <div style="flex: 1; min-width: 200px;">
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
            <div style="display: grid; grid-template-columns: repeat({{ count($comparisonData) }}, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
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

                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
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
                    $barColors = ['#d4920c', '#e8b04a', '#f5d88e'];
                @endphp
                @foreach ($chartMetrics as $idx => $metric)
                    @php $maxVal = max(array_column($comparisonData, $metric)) ?: 1; @endphp
                    <div style="margin-bottom: 1.5rem;">
                        <x-central.eyebrow class="mb-2">{{ $chartLabels[$idx] }}</x-central.eyebrow>
                        @foreach ($comparisonData as $tIdx => $tenant)
                            @php $pct = round(($tenant[$metric] / $maxVal) * 100); @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                                <span style="color: #faf0d6; font-size: 0.75rem; width: 120px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tenant['name'] }}</span>
                                <div style="flex: 1; background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColors[$tIdx % 3] }}; border-radius: 4px; transition: width 0.3s;"></div>
                                </div>
                                <span style="color: #faf0d6; font-size: 0.8rem; font-weight: 700; width: 50px;">{{ $tenant[$metric] }}</span>
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
                <div style="color: white; font-weight: 700; font-size: 1rem; text-align: center; margin-bottom: 1.5rem;">
                    <x-heroicon-s-star class="w-5 h-5 inline-block align-middle mr-1 text-honey" />
                    Top 3 Bakeries
                </div>
                <div style="display: flex; align-items: flex-end; justify-content: center; gap: 1rem; padding-top: 1rem;">
                    <div style="text-align: center; width: 160px;">
                        <div style="color: #faf0d6; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $top3[1]['name'] }}</div>
                        <div style="color: #8b6844; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[1]['total_orders'] }} orders</div>
                        <div style="background: linear-gradient(180deg, #94a3b8, #64748b); height: {{ $podiumHeights[1] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 1.75rem; font-weight: 700;">#2</span>
                        </div>
                    </div>
                    <div style="text-align: center; width: 180px;">
                        <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $top3[0]['name'] }}</div>
                        <div style="color: #d4920c; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[0]['total_orders'] }} orders</div>
                        <div style="background: linear-gradient(180deg, #e8b04a, #d4920c); height: {{ $podiumHeights[0] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 1.75rem; font-weight: 700;">#1</span>
                        </div>
                    </div>
                    <div style="text-align: center; width: 160px;">
                        <div style="color: #faf0d6; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $top3[2]['name'] }}</div>
                        <div style="color: #8b6844; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[2]['total_orders'] }} orders</div>
                        <div style="background: linear-gradient(180deg, #b45309, #92400e); height: {{ $podiumHeights[2] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 1.75rem; font-weight: 700;">#3</span>
                        </div>
                    </div>
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
