<x-filament-panels::page>
    @if (! $this->getHasData())
        {{-- Empty State --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 3rem; text-align: center;">
            <div style="margin-bottom: 1rem;">
                <svg style="width: 48px; height: 48px; display: inline-block;" viewBox="0 0 24 24" fill="none" stroke="#d4920c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">No Usage Data Yet</div>
            <p style="color: #8b6844; max-width: 480px; margin: 0 auto 1rem;">
                Feature usage tracking hasn't recorded any data yet. Once tracking middleware is added to the tenant application,
                you'll see detailed analytics about which features your bakeries use most.
            </p>
            <div style="background: #2a1f18; border-radius: 8px; padding: 1rem; display: inline-block; text-align: left;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Features that will be tracked</div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                    @foreach (['quick_order', 'recipe_calculator', 'shopping_list', 'instagram_captions', 'delivery_planner', 'baking_sheet', 'order_calendar', 'review_analytics', 'storefront'] as $feature)
                        <span style="display: inline-block; background: rgba(212,146,12,0.1); color: #faf0d6; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600;">{{ str_replace('_', ' ', ucfirst($feature)) }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        {{-- Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Most Used Feature</div>
                <div style="color: white; font-size: 1.25rem; font-weight: 700;">{{ $this->formatFeatureName($this->getMostUsedFeature() ?? '—') }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Least Used Feature</div>
                <div style="color: white; font-size: 1.25rem; font-weight: 700;">{{ $this->formatFeatureName($this->getLeastUsedFeature() ?? '—') }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Total Interactions This Month</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ number_format($this->getTotalInteractionsThisMonth()) }}</div>
            </div>
        </div>

        {{-- Bar Chart --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Usage by Feature</div>
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
        </div>

        {{-- Per-feature tenant breakdown --}}
        @if ($this->selectedFeature)
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">
                    {{ $this->formatFeatureName($this->selectedFeature) }} — Top Tenants
                </div>
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
            </div>
        @endif

        {{-- Heatmap --}}
        @php $heatmap = $this->getHeatmapData(); @endphp
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; overflow-x: auto;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">7-Day Usage Heatmap</div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 0.5rem 0.75rem; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Feature</th>
                        @foreach ($heatmap['days'] as $day)
                            <th style="text-align: center; padding: 0.5rem 0.4rem; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($heatmap['rows'] as $row)
                        <tr>
                            <td style="padding: 0.4rem 0.75rem; color: #faf0d6; font-size: 0.8rem; white-space: nowrap;">{{ $this->formatFeatureName($row['feature']) }}</td>
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
                                <td style="text-align: center; padding: 0.4rem;" title="{{ $cell['count'] }} uses on {{ $cell['date'] }}">
                                    <div style="background: {{ $bg }}; border-radius: 4px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; color: {{ $i >= 0.5 ? '#1c1410' : '#8b6844' }}; font-size: 0.7rem; font-weight: 600;">
                                        {{ $cell['count'] ?: '' }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
