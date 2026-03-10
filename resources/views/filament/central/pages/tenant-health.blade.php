<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
        $tenants = $this->getTenantHealthData();
    @endphp

    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Average Health</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: white;">{{ $stats['average'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Healthy (&gt;70)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #10b981;">{{ $stats['healthy'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">At Risk (40–70)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b;">{{ $stats['at_risk'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Critical (&lt;40)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444;">{{ $stats['critical'] }}</div>
        </div>
    </div>

    {{-- Tenant Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem;">
        @foreach ($tenants as $tenant)
            @php
                $score = $tenant['health_score'];
                $color = $score > 70 ? '#10b981' : ($score >= 40 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; border-left: 4px solid {{ $color }}; transition: transform 0.2s, box-shadow 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div>
                        <div style="font-weight: 700; color: white; font-size: 1rem;">{{ $tenant['name'] }}</div>
                        <div style="color: #8b6844; font-size: 0.8rem;">{{ $tenant['owner'] }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: {{ $color }}; line-height: 1;">{{ $score }}</div>
                        <div style="font-size: 0.7rem; color: #8b6844;">/100</div>
                    </div>
                </div>

                {{-- Plan Badge --}}
                <span style="display: inline-block; background: rgba(212,146,12,0.1); color: #d4920c; border: 1px solid rgba(212,146,12,0.25); border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; margin-bottom: 1rem;">
                    {{ $tenant['plan'] }}
                </span>

                {{-- Breakdown Bars --}}
                @php
                    $factors = [
                        ['label' => 'Login Recency', 'value' => $tenant['login_score'], 'max' => 25],
                        ['label' => 'Orders', 'value' => $tenant['order_score'], 'max' => 25],
                        ['label' => 'Products', 'value' => $tenant['product_score'], 'max' => 20],
                        ['label' => 'Setup', 'value' => $tenant['setup_score'], 'max' => 30],
                    ];
                @endphp
                @foreach ($factors as $factor)
                    <div style="margin-bottom: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #8b6844; margin-bottom: 0.2rem;">
                            <span>{{ $factor['label'] }}</span>
                            <span>{{ $factor['value'] }}/{{ $factor['max'] }}</span>
                        </div>
                        <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="height: 100%; border-radius: 4px; background: {{ $color }}; width: {{ $factor['max'] > 0 ? round(($factor['value'] / $factor['max']) * 100) : 0 }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
