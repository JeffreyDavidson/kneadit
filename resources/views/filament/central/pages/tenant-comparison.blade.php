<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div class="mb-6 flex gap-2">
        @foreach ([
            'compare' => 'Compare',
            'leaderboard' => 'Leaderboard',
        ] as $key => $label)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-honey/25 cursor-pointer',
                    'bg-honey text-warm-black' => $activeTab === $key,
                    'bg-transparent text-honey' => $activeTab !== $key,
                ])
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
            $leaderboardForPresets = $this->getLeaderboardData();
            $topPerformers = array_slice(
                array_values(array_filter($leaderboardForPresets, fn (array $t) => ($t['total_orders'] ?? 0) > 0)),
                0,
                3,
            );
            $topPerformerIds = array_column($topPerformers, 'id');
            $hasTopPresetEnough = count($topPerformerIds) >= 2;
            $topPresetQuery = $hasTopPresetEnough
                ? http_build_query(['tenants' => $topPerformerIds])
                : null;
        @endphp

        <div
            x-data="{
            bakery1: '{{ $this->selectedTenants[0] ?? '' }}',
            bakery2: '{{ $this->selectedTenants[1] ?? '' }}',
            bakery3: '{{ $this->selectedTenants[2] ?? '' }}',
            get selectedCount() {
                return [this.bakery1, this.bakery2, this.bakery3].filter(Boolean).length;
            },
            clear(slot) { this['bakery' + slot] = ''; },
            compare() {
                if (this.selectedCount < 2) return;
                const params = new URLSearchParams();
                [this.bakery1, this.bakery2, this.bakery3].forEach(id => {
                    if (id) params.append('tenants[]', id);
                });
                window.location.search = params.toString();
            }
        }"
        >
            <x-central.card class="mb-6">
                <div class="mb-4 flex items-center justify-between">
                    <x-central.eyebrow>Select Bakeries to Compare</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]" x-text="selectedCount + ' of 3 selected · min 2'"></span>
                </div>
                <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                    @for ($i = 1; $i <= 3; $i++)
                        <div>
                            <label
                                for="bakery-{{ $i }}"
                                class="text-cinnamon mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] uppercase"
                            >Bakery {{ $i }}</label>
                            <div class="flex items-stretch gap-1.5">
                                <x-central.select id="bakery-{{ $i }}" x-model="bakery{{ $i }}" class="min-w-0 flex-1">
                                    <option value="">— Select —</option>
                                    @foreach ($allTenants as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </x-central.select>
                                <button
                                    type="button"
                                    @click="clear({{ $i }})"
                                    x-show="bakery{{ $i }}"
                                    x-cloak
                                    class="bg-espresso border-honey/12 text-cinnamon hover:text-honey hover:border-honey/40 inline-flex w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border transition-colors"
                                    title="Clear selection"
                                >
                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($hasTopPresetEnough)
                            <a
                                href="?{{ $topPresetQuery }}"
                                class="bg-espresso text-honey border-honey/25 hover:border-honey inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold no-underline transition-colors"
                            >
                                <x-heroicon-o-trophy class="h-3.5 w-3.5" />
                                Top {{ count($topPerformerIds) }} performers
                            </a>
                        @endif
                    </div>
                    <button
                        type="button"
                        @click="compare()"
                        :disabled="selectedCount < 2"
                        :class="selectedCount >= 2
                            ? 'bg-honey text-warm-black hover:bg-golden cursor-pointer'
                            : 'bg-espresso text-cinnamon cursor-not-allowed'"
                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-[0.8rem] font-bold transition-colors"
                    >
                        <x-heroicon-o-chart-bar-square class="h-4 w-4" />
                        Compare
                    </button>
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
                        <div class="border-honey/8 mb-4 border-b pb-4 text-center">
                            <div class="mb-2 text-base font-bold text-white">{{ $tenant['name'] }}</div>
                            <x-central.badge :color="$planColor">{{ $tenant['plan'] }}</x-central.badge>
                        </div>

                        <div class="flex flex-1 flex-col gap-2">
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
                            <x-central.metric-row label="Health Score" :value-class="$healthColor . ' font-bold'">
                                {{ $tenant['health_score'] }}/100</x-central.metric-row>
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
                            <div class="mb-1.5 flex items-center gap-3">
                                <span class="text-parchment w-[120px] truncate text-right text-xs">{{ $tenant['name'] }}</span>
                                <div class="bg-espresso h-2 flex-1 overflow-hidden rounded">
                                    <div
                                        class="h-full rounded transition-all duration-300 {{ $barClasses[$tIdx % 3] }}"
                                        style="width: {{ $pct }}%;"
                                    ></div>
                                </div>
                                <span class="text-parchment w-[50px] text-[0.8rem] font-bold">{{ $tenant[$metric] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </x-central.card>
        @else
            <x-central.card padding="py-12 px-8" class="text-center">
                <div class="mb-4">
                    <x-heroicon-o-chart-bar class="text-honey inline-block h-12 w-12" />
                </div>
                <div class="text-[1.05rem] font-semibold text-white">Pick 2–3 bakeries to compare</div>
                <div class="text-cinnamon mx-auto mt-2 max-w-[520px] text-[0.85rem]">
                    See their orders, products, reviews, and health side by side. Handy for spotting what healthy
                    bakeries do differently from struggling ones.
                </div>
                @if ($hasTopPresetEnough)
                    <div class="mt-6">
                        <a
                            href="?{{ $topPresetQuery }}"
                            class="bg-honey text-warm-black hover:bg-golden inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-[0.8rem] font-bold no-underline transition-colors"
                        >
                            <x-heroicon-o-trophy class="h-4 w-4" />
                            Start with top {{ count($topPerformerIds) }} performers
                        </a>
                    </div>
                @endif
            </x-central.card>
        @endif
    @endif

    {{-- Leaderboard Tab --}}
    @if ($activeTab === 'leaderboard')
        @php
            $leaderboard = $this->getLeaderboardData();
            $summary = $this->getLeaderboardSummaryStats();
            $activeEntries = array_values(array_filter($leaderboard, fn (array $t) => ($t['total_orders'] ?? 0) > 0));
            $hasRealPodium = count($activeEntries) >= 3;
            $topPerformer = $activeEntries[0] ?? null;
        @endphp

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-central.stat-card label="Total Platform Orders" value-class="text-[1.75rem] text-white">
                {{ number_format($summary['total_orders']) }}</x-central.stat-card>
            <x-central.stat-card label="Active Bakeries" value-class="text-[1.75rem] text-white">
                {{ $summary['active_bakeries'] }}
                <span class="text-cinnamon text-[0.9rem] font-semibold">of {{ $summary['total_bakeries'] }}</span>
            </x-central.stat-card>
            <x-central.stat-card label="Avg Orders / Active Bakery" value-class="text-[1.75rem] text-white">
                {{ $summary['avg_orders_active'] }}</x-central.stat-card>
        </div>

        @if ($hasRealPodium)
            <x-central.card class="mb-6">
                <div class="mb-6 text-center text-base font-bold text-white">
                    <x-heroicon-s-star class="text-honey mr-1 inline-block h-5 w-5 align-middle" />
                    Top 3 Bakeries
                </div>
                @php
                    $podiumHeights = [140, 110, 90];
                    $podium = [
                        ['rank' => 2, 'tenant' => $activeEntries[1], 'width' => 'w-40', 'gradient' => 'from-slate-400 to-slate-500', 'nameColor' => 'text-parchment', 'ordersColor' => 'text-cinnamon', 'fontSize' => 'text-[0.875rem]'],
                        ['rank' => 1, 'tenant' => $activeEntries[0], 'width' => 'w-[180px]', 'gradient' => 'from-golden to-honey', 'nameColor' => 'text-white', 'ordersColor' => 'text-honey', 'fontSize' => 'text-base'],
                        ['rank' => 3, 'tenant' => $activeEntries[2], 'width' => 'w-40', 'gradient' => 'from-amber-700 to-amber-800', 'nameColor' => 'text-parchment', 'ordersColor' => 'text-cinnamon', 'fontSize' => 'text-[0.875rem]'],
                    ];
                @endphp
                <div class="flex items-end justify-center gap-4 pt-4">
                    @foreach ($podium as $idx => $entry)
                        <div class="text-center {{ $entry['width'] }}">
                            <div class="font-bold mb-2 {{ $entry['nameColor'] }} {{ $entry['fontSize'] }}">
                                {{ $entry['tenant']['name'] }}
                            </div>
                            <div class="text-xs mb-2 {{ $entry['ordersColor'] }}">
                                {{ $entry['tenant']['total_orders'] }} orders
                            </div>
                            <div
                                class="rounded-t-lg flex items-center justify-center bg-gradient-to-b {{ $entry['gradient'] }}"
                                style="height: {{ $podiumHeights[match($entry['rank']) { 1 => 0, 2 => 1, 3 => 2 }] }}px;"
                            >
                                <span class="text-[1.75rem] font-bold text-white">#{{ $entry['rank'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-central.card>
        @elseif ($topPerformer)
            <x-central.card class="bg-honey/5 border-honey/25 mb-6">
                <div class="flex items-center gap-4">
                    <div class="bg-honey/15 border-honey/25 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border">
                        <x-heroicon-s-trophy class="text-honey h-6 w-6" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <x-central.eyebrow>Top Performer</x-central.eyebrow>
                        <div class="mt-0.5 truncate text-[1.05rem] font-bold text-white">
                            {{ $topPerformer['name'] }}
                        </div>
                        <div class="text-cinnamon text-[0.8rem]">
                            {{ $topPerformer['total_orders'] }} {{ Illuminate\Support\Str::plural('order', $topPerformer['total_orders']) }} · {{ $summary['active_bakeries'] }} of {{ $summary['total_bakeries'] }} bakeries
                            have made a sale
                        </div>
                    </div>
                </div>
            </x-central.card>
        @endif

        <x-central.card padding="p-0" class="overflow-hidden">
            <div class="border-honey/8 border-b px-6 py-6">
                <div class="text-base font-bold text-white">Full Rankings</div>
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
                            <x-central.td align="right" tone="white" class="font-bold">
                                {{ $tenant['total_orders'] }}</x-central.td>
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
