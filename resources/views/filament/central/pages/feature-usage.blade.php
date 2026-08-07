<x-filament-panels::page>
    @if (! $this->getHasData())
        {{-- Empty State --}}
        <x-central.card padding="p-12" class="text-center">
            <div class="mb-4">
                <x-heroicon-o-chart-bar class="text-honey inline-block h-12 w-12" />
            </div>
            <div class="mb-2 text-base font-bold text-white">No Usage Data Yet</div>
            <p class="text-cinnamon mx-auto mb-4 max-w-[480px]">
                Feature usage tracking hasn't recorded any data yet. Once tracking middleware is added to the tenant
                application, you'll see detailed analytics about which features your bakeries use most.
            </p>
            <div class="bg-espresso inline-block rounded-lg p-4 text-left">
                <x-central.eyebrow class="mb-2">Features that will be tracked</x-central.eyebrow>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['quick_order', 'recipe_calculator', 'shopping_list', 'instagram_captions', 'delivery_planner', 'baking_sheet', 'order_calendar', 'review_analytics', 'storefront'] as $feature)
                        <x-central.badge color="honey-soft-light" :uppercase="false">
                            {{ str_replace('_', ' ', ucfirst($feature)) }}</x-central.badge>
                    @endforeach
                </div>
            </div>
        </x-central.card>
    @else
        @php
            $mostUsed = $this->getMostUsedFeature();
            $leastUsed = $this->getLeastUsedFeature();
            $thisMonth = $this->getTotalInteractionsThisMonth();
            $allTime = $this->getTotalInteractionsAllTime();
            $mostUsedCount = $this->getFeatureTotalCount($mostUsed);
            $leastUsedCount = $this->getFeatureTotalCount($leastUsed);
            $monthShare = $allTime > 0 ? round(($thisMonth / $allTime) * 100) : 0;
        @endphp

        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-central.card class="bg-honey/5 border-honey/20">
                <div class="mb-2 flex items-center gap-3">
                    <div class="bg-honey/15 border-honey/25 flex h-9 w-9 items-center justify-center rounded-xl border">
                        <x-heroicon-o-fire class="text-honey h-4 w-4" />
                    </div>
                    <x-central.eyebrow>Most used</x-central.eyebrow>
                </div>
                <div class="truncate text-[1.1rem] font-bold text-white">
                    {{ $this->formatFeatureName($mostUsed ?? '—') }}
                </div>
                <div class="text-cinnamon mt-1 text-[0.75rem]">{{ number_format($mostUsedCount) }} all-time uses</div>
            </x-central.card>

            <x-central.card>
                <div class="mb-2 flex items-center gap-3">
                    <div class="bg-cinnamon/15 border-cinnamon/25 flex h-9 w-9 items-center justify-center rounded-xl border">
                        <x-heroicon-o-moon class="text-cinnamon h-4 w-4" />
                    </div>
                    <x-central.eyebrow>Least used</x-central.eyebrow>
                </div>
                <div class="truncate text-[1.1rem] font-bold text-white">
                    {{ $this->formatFeatureName($leastUsed ?? '—') }}
                </div>
                <div class="text-cinnamon mt-1 text-[0.75rem]">{{ number_format($leastUsedCount) }} all-time uses</div>
            </x-central.card>

            <x-central.card>
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/15">
                        <x-heroicon-o-calendar class="h-4 w-4 text-emerald-400" />
                    </div>
                    <x-central.eyebrow>This month</x-central.eyebrow>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[1.75rem] leading-none font-bold text-white">{{ number_format($thisMonth) }}</span>
                    <span class="text-cinnamon text-[0.75rem]">interactions</span>
                </div>
                <div class="text-cinnamon mt-1 text-[0.75rem]">
                    {{ $monthShare }}% of {{ number_format($allTime) }} all-time
                </div>
            </x-central.card>
        </div>

        {{-- Bar Chart --}}
        <x-central.card title="Usage by Feature" class="mb-6">
            <div class="flex flex-col gap-2.5">
                @foreach ($this->getFeatureUsageBars() as $bar)
                    @php $isSelected = $this->selectedFeature === $bar['feature']; @endphp
                    <button
                        type="button"
                        wire:click="selectFeature('{{ $bar['feature'] }}')"
                        class="w-full flex items-center gap-3 text-left rounded-lg px-2 py-1.5 -mx-2 hover:bg-honey/5 transition-colors cursor-pointer {{ $isSelected ? 'bg-honey/10' : '' }}"
                    >
                        <div class="w-40 flex-shrink-0 text-right">
                            <span @class([
                                'text-[0.8rem]',
                                'text-honey font-bold' => $isSelected,
                                'text-cinnamon font-normal' => ! $isSelected,
                            ])>
                                {{ $this->formatFeatureName($bar['feature']) }}
                            </span>
                        </div>
                        <div class="bg-espresso h-2 flex-1 overflow-hidden rounded-full">
                            <div
                                class="from-honey to-golden h-full min-w-[4px] rounded-full bg-gradient-to-r"
                                style="width: {{ $bar['percent'] }}%;"
                            ></div>
                        </div>
                        <span class="text-parchment w-[60px] text-right text-xs font-bold tabular-nums">{{ number_format($bar['total']) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="text-cinnamon mt-3 text-[0.7rem]">Click a feature to see which bakeries use it most.</div>
        </x-central.card>

        {{-- Per-feature tenant breakdown --}}
        @if ($this->selectedFeature)
            <x-central.card class="mb-6">
                <div class="mb-4 flex items-center justify-between">
                    <x-central.eyebrow>
                        {{ $this->formatFeatureName($this->selectedFeature) }} — top bakeries</x-central.eyebrow>
                    <button
                        type="button"
                        wire:click="selectFeature(null)"
                        class="text-cinnamon hover:text-honey inline-flex cursor-pointer items-center gap-1 text-[0.7rem] transition-colors"
                    >
                        <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                        Clear
                    </button>
                </div>
                @php $breakdown = $this->getFeatureTenantBreakdown(); @endphp
                @if ($breakdown->isEmpty())
                    <p class="text-cinnamon text-[0.85rem]">No bakery data available for this feature yet.</p>
                @else
                    @php $topUses = $breakdown->max('total') ?: 1; @endphp
                    <div class="flex flex-col gap-2">
                        @foreach ($breakdown as $row)
                            @php $pct = round(($row['total'] / $topUses) * 100); @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-parchment w-[180px] truncate text-[0.85rem]">{{ $row['name'] }}</span>
                                <div class="bg-espresso h-2 flex-1 overflow-hidden rounded-full">
                                    <div
                                        class="from-honey to-golden h-full rounded-full bg-gradient-to-r"
                                        style="width: {{ $pct }}%;"
                                    ></div>
                                </div>
                                <span class="text-honey w-[60px] text-right text-[0.75rem] font-bold tabular-nums">{{ number_format($row['total']) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-central.card>
        @endif

        {{-- Heatmap --}}
        @php
            $heatmap = $this->getHeatmapData();
            $heatmapHasActivity = collect($heatmap['rows'])->some(fn ($r) => collect($r['cells'])->some(fn ($c) => $c['count'] > 0));
        @endphp
        <x-central.card class="overflow-x-auto">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <x-central.eyebrow>7-day usage heatmap</x-central.eyebrow>
                <div class="text-cinnamon flex items-center gap-2 text-[0.7rem]">
                    <span>Less</span>
                    <div class="flex gap-0.5">
                        <span class="h-3 w-3 rounded-sm" style="background: #2a1f18"></span>
                        <span class="h-3 w-3 rounded-sm" style="background: #3d2c1e"></span>
                        <span class="h-3 w-3 rounded-sm" style="background: #6b4c1e"></span>
                        <span class="h-3 w-3 rounded-sm" style="background: #d4920c"></span>
                        <span class="h-3 w-3 rounded-sm" style="background: #e8b04a"></span>
                    </div>
                    <span>More</span>
                </div>
            </div>

            @if (! $heatmapHasActivity)
                <div class="text-cinnamon py-6 text-center text-[0.85rem]">
                    No feature activity recorded in the last 7 days.
                </div>
            @else
                <x-central.table>
                    <thead>
                        <x-central.tr :border="false">
                            <x-central.eyebrow as="th" class="px-3 py-2 text-left">Feature</x-central.eyebrow>
                            @foreach ($heatmap['days'] as $day)
                                <x-central.eyebrow as="th" class="px-1.5 py-2 text-center">
                                    {{ $day }}</x-central.eyebrow>
                            @endforeach
                        </x-central.tr>
                    </thead>
                    <tbody>
                        @foreach ($heatmap['rows'] as $row)
                            <x-central.tr :border="false">
                                <x-central.td padding="py-1.5 px-3" class="text-[0.8rem] whitespace-nowrap">
                                    {{ $this->formatFeatureName($row['feature']) }}</x-central.td>
                                @foreach ($row['cells'] as $cell)
                                    @php
                                        $i = $cell['intensity'];
                                        if ($cell['count'] === 0) {
                                            $bg = '#2a1f18';
                                        } elseif ($i < 0.25) {
                                            $bg = '#3d2c1e';
                                        } elseif ($i < 0.5) {
                                            $bg = '#6b4c1e';
                                        } elseif ($i < 0.75) {
                                            $bg = '#d4920c';
                                        } else {
                                            $bg = '#e8b04a';
                                        }
                                    @endphp
                                    <x-central.td
                                        align="center"
                                        padding="p-1.5"
                                        title="{{ $cell['count'] }} uses on {{ $cell['date'] }}"
                                    >
                                        <div
                                            style="background: {{ $bg }}; color: {{ $i >= 0.5 ? '#1c1410' : '#8b6844' }};"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded text-[0.7rem] font-semibold"
                                        >
                                            {{ $cell['count'] ?: '' }}
                                        </div>
                                    </x-central.td>
                                @endforeach
                            </x-central.tr>
                        @endforeach
                    </tbody>
                </x-central.table>
            @endif
        </x-central.card>
    @endif
</x-filament-panels::page>
