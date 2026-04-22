<x-filament-panels::page>
    @if (! $this->getHasData())
        {{-- Empty State --}}
        <x-central.card padding="p-12" class="text-center">
            <div class="mb-4">
                <x-heroicon-o-chart-bar class="w-12 h-12 inline-block text-honey" />
            </div>
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">No Usage Data Yet</div>
            <p style="color: #8b6844; max-width: 480px; margin: 0 auto 1rem;">
                Feature usage tracking hasn't recorded any data yet. Once tracking middleware is added to the tenant application,
                you'll see detailed analytics about which features your bakeries use most.
            </p>
            <div style="background: #2a1f18; border-radius: 8px; padding: 1rem; display: inline-block; text-align: left;">
                <x-central.eyebrow class="mb-2">Features that will be tracked</x-central.eyebrow>
                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
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
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                @foreach ($this->getFeatureUsageBars() as $bar)
                    <div wire:click="selectFeature('{{ $bar['feature'] }}')" style="cursor: pointer; display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 160px; flex-shrink: 0; text-align: right;">
                            <span style="color: {{ $this->selectedFeature === $bar['feature'] ? '#e8b04a' : '#8b6844' }}; font-size: 0.8rem; font-weight: {{ $this->selectedFeature === $bar['feature'] ? '700' : '400' }};">
                                {{ $this->formatFeatureName($bar['feature']) }}
                            </span>
                        </div>
                        <div style="flex: 1; background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="width: {{ $bar['percent'] }}%; background: linear-gradient(90deg, #d4920c, #e8b04a); height: 100%; border-radius: 4px; min-width: 4px;"></div>
                        </div>
                        <span style="color: #faf0d6; font-size: 0.75rem; font-weight: 700; width: 50px; text-align: right;">{{ number_format($bar['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </x-central.card>

        {{-- Per-feature tenant breakdown --}}
        @if ($this->selectedFeature)
            <x-central.card :title="$this->formatFeatureName($this->selectedFeature).' — Top Tenants'" class="mb-6">
                @php $breakdown = $this->getFeatureTenantBreakdown(); @endphp
                @if ($breakdown->isEmpty())
                    <p style="color: #8b6844;">No tenant data available.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach ($breakdown as $row)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: #2a1f18; border-radius: 8px; padding: 0.6rem 1rem;">
                                <span style="color: #faf0d6; font-size: 0.875rem;">{{ $row->tenant_id }}</span>
                                <span style="color: #d4920c; font-weight: 700; font-size: 0.875rem;">{{ number_format($row->total) }} uses</span>
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
