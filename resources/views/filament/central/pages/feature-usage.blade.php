<x-filament-panels::page>
    @if(! $this->getHasData())
        {{-- Empty State --}}
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 48px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
            <h3 style="color: #e8b04a; font-size: 20px; font-weight: 600; margin-bottom: 8px;">No Usage Data Yet</h3>
            <p style="color: #a89580; max-width: 480px; margin: 0 auto 16px;">
                Feature usage tracking hasn't recorded any data yet. Once tracking middleware is added to the tenant application,
                you'll see detailed analytics about which features your bakeries use most.
            </p>
            <div style="background: #2a1f18; border-radius: 8px; padding: 16px; display: inline-block; text-align: left;">
                <p style="color: #d4920c; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Features that will be tracked:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @foreach(['quick_order', 'recipe_calculator', 'shopping_list', 'instagram_captions', 'delivery_planner', 'baking_sheet', 'order_calendar', 'review_analytics', 'storefront'] as $feature)
                        <span style="background: #3d2c1e; color: #f5d88e; padding: 4px 10px; border-radius: 6px; font-size: 12px;">{{ str_replace('_', ' ', ucfirst($feature)) }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        {{-- Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px;">
                <p style="color: #a89580; font-size: 13px; margin-bottom: 4px;">Most Used Feature</p>
                <p style="color: #e8b04a; font-size: 22px; font-weight: 700;">{{ $this->formatFeatureName($this->getMostUsedFeature() ?? '—') }}</p>
            </div>
            <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px;">
                <p style="color: #a89580; font-size: 13px; margin-bottom: 4px;">Least Used Feature</p>
                <p style="color: #e8b04a; font-size: 22px; font-weight: 700;">{{ $this->formatFeatureName($this->getLeastUsedFeature() ?? '—') }}</p>
            </div>
            <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px;">
                <p style="color: #a89580; font-size: 13px; margin-bottom: 4px;">Total Interactions This Month</p>
                <p style="color: #e8b04a; font-size: 22px; font-weight: 700;">{{ number_format($this->getTotalInteractionsThisMonth()) }}</p>
            </div>
        </div>

        {{-- Bar Chart --}}
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
            <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Usage by Feature</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($this->getFeatureUsageBars() as $bar)
                    <div wire:click="selectFeature('{{ $bar['feature'] }}')" style="cursor: pointer; display: flex; align-items: center; gap: 12px;">
                        <div style="width: 160px; flex-shrink: 0; text-align: right;">
                            <span style="color: {{ $this->selectedFeature === $bar['feature'] ? '#f5d88e' : '#a89580' }}; font-size: 13px; font-weight: {{ $this->selectedFeature === $bar['feature'] ? '600' : '400' }};">
                                {{ $this->formatFeatureName($bar['feature']) }}
                            </span>
                        </div>
                        <div style="flex: 1; background: #2a1f18; border-radius: 6px; height: 28px; overflow: hidden;">
                            <div style="width: {{ $bar['percent'] }}%; background: linear-gradient(90deg, #d4920c, #e8b04a); height: 100%; border-radius: 6px; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; min-width: 40px;">
                                <span style="color: #0c0a09; font-size: 12px; font-weight: 700;">{{ number_format($bar['total']) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Per-feature tenant breakdown --}}
        @if($this->selectedFeature)
            <div style="background: #1c1410; border: 1px solid #d4920c; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                    {{ $this->formatFeatureName($this->selectedFeature) }} — Top Tenants
                </h3>
                @php $breakdown = $this->getFeatureTenantBreakdown(); @endphp
                @if($breakdown->isEmpty())
                    <p style="color: #a89580;">No tenant data available.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($breakdown as $row)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: #2a1f18; border-radius: 8px; padding: 10px 16px;">
                                <span style="color: #f5d88e; font-size: 14px;">{{ $row->tenant_id }}</span>
                                <span style="color: #d4920c; font-weight: 700; font-size: 14px;">{{ number_format($row->total) }} uses</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Heatmap --}}
        @php $heatmap = $this->getHeatmapData(); @endphp
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px; overflow-x: auto;">
            <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; margin-bottom: 16px;">7-Day Usage Heatmap</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 8px 12px; color: #a89580; font-size: 12px; font-weight: 600;">Feature</th>
                        @foreach($heatmap['days'] as $day)
                            <th style="text-align: center; padding: 8px 6px; color: #a89580; font-size: 12px; font-weight: 600;">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($heatmap['rows'] as $row)
                        <tr>
                            <td style="padding: 6px 12px; color: #f5d88e; font-size: 13px; white-space: nowrap;">{{ $this->formatFeatureName($row['feature']) }}</td>
                            @foreach($row['cells'] as $cell)
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
                                <td style="text-align: center; padding: 6px;" title="{{ $cell['count'] }} uses on {{ $cell['date'] }}">
                                    <div style="background: {{ $bg }}; border-radius: 4px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; color: {{ $i >= 0.5 ? '#0c0a09' : '#a89580' }}; font-size: 11px; font-weight: 600;">
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
