<x-filament-panels::page>
    @if (! $this->getHasData())
        {{-- Empty State --}}
        <x-central.card padding="p-12" class="text-center">
            <div class="mb-4">
                <x-heroicon-o-chart-bar class="w-12 h-12 inline-block text-honey" />
            </div>
            <div class="text-white font-bold text-base mb-2">No Usage Data Yet</div>
            <p class="text-cinnamon max-w-[480px] mx-auto mb-4">
                Feature usage tracking hasn't recorded any data yet. Once tracking middleware is added to the tenant application,
                you'll see detailed analytics about which features your bakeries use most.
            </p>
            <div class="bg-espresso rounded-lg p-4 inline-block text-left">
                <x-central.eyebrow class="mb-2">Features that will be tracked</x-central.eyebrow>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['quick_order', 'recipe_calculator', 'shopping_list', 'instagram_captions', 'delivery_planner', 'baking_sheet', 'order_calendar', 'review_analytics', 'storefront'] as $feature)
                        <x-central.badge color="honey-soft-light" :uppercase="false">{{ str_replace('_', ' ', ucfirst($feature)) }}</x-central.badge>
                    @endforeach
                </div>
            </div>
        </x-central.card>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <x-central.stat-card label="Most Used Feature" value-class="text-[1.25rem] text-white">{{ $this->formatFeatureName($this->getMostUsedFeature() ?? '—') }}</x-central.stat-card>
            <x-central.stat-card label="Least Used Feature" value-class="text-[1.25rem] text-white">{{ $this->formatFeatureName($this->getLeastUsedFeature() ?? '—') }}</x-central.stat-card>
            <x-central.stat-card label="Total Interactions This Month" value-class="text-[1.75rem] text-white">{{ number_format($this->getTotalInteractionsThisMonth()) }}</x-central.stat-card>
        </div>

        {{-- Bar Chart --}}
        <x-central.card title="Usage by Feature" class="mb-6">
            <div class="flex flex-col gap-2.5">
                @foreach ($this->getFeatureUsageBars() as $bar)
                    @php $isSelected = $this->selectedFeature === $bar['feature']; @endphp
                    <div wire:click="selectFeature('{{ $bar['feature'] }}')" class="cursor-pointer flex items-center gap-3">
                        <div class="w-40 flex-shrink-0 text-right">
                            <span @class([
                                'text-[0.8rem]',
                                'text-golden font-bold' => $isSelected,
                                'text-cinnamon font-normal' => ! $isSelected,
                            ])>
                                {{ $this->formatFeatureName($bar['feature']) }}
                            </span>
                        </div>
                        <div class="flex-1 bg-espresso rounded h-2 overflow-hidden">
                            <div class="h-full rounded min-w-[4px] bg-gradient-to-r from-honey to-golden" style="width: {{ $bar['percent'] }}%;"></div>
                        </div>
                        <span class="text-parchment text-xs font-bold w-[50px] text-right">{{ number_format($bar['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </x-central.card>

        {{-- Per-feature tenant breakdown --}}
        @if ($this->selectedFeature)
            <x-central.card :title="$this->formatFeatureName($this->selectedFeature).' — Top Tenants'" class="mb-6">
                @php $breakdown = $this->getFeatureTenantBreakdown(); @endphp
                @if ($breakdown->isEmpty())
                    <p class="text-cinnamon">No tenant data available.</p>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach ($breakdown as $row)
                            <div class="flex items-center justify-between bg-espresso rounded-lg px-4 py-2.5">
                                <span class="text-parchment text-sm">{{ $row->tenant_id }}</span>
                                <span class="text-honey font-bold text-sm">{{ number_format($row->total) }} uses</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-central.card>
        @endif

        {{-- Heatmap --}}
        @php $heatmap = $this->getHeatmapData(); @endphp
        <x-central.card title="7-Day Usage Heatmap" class="overflow-x-auto">
            <x-central.table>
                <thead>
                    <x-central.tr :border="false">
                        <x-central.eyebrow as="th" class="text-left py-2 px-3">Feature</x-central.eyebrow>
                        @foreach ($heatmap['days'] as $day)
                            <x-central.eyebrow as="th" class="text-center py-2 px-1.5">{{ $day }}</x-central.eyebrow>
                        @endforeach
                    </x-central.tr>
                </thead>
                <tbody>
                    @foreach ($heatmap['rows'] as $row)
                        <x-central.tr :border="false">
                            <x-central.td padding="py-1.5 px-3" class="text-[0.8rem] whitespace-nowrap">{{ $this->formatFeatureName($row['feature']) }}</x-central.td>
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
                                <x-central.td align="center" padding="p-1.5" title="{{ $cell['count'] }} uses on {{ $cell['date'] }}">
                                    <div style="background: {{ $bg }}; color: {{ $i >= 0.5 ? '#1c1410' : '#8b6844' }};" class="rounded w-9 h-9 inline-flex items-center justify-center text-[0.7rem] font-semibold">
                                        {{ $cell['count'] ?: '' }}
                                    </div>
                                </x-central.td>
                            @endforeach
                        </x-central.tr>
                    @endforeach
                </tbody>
            </x-central.table>
        </x-central.card>
    @endif
</x-filament-panels::page>
