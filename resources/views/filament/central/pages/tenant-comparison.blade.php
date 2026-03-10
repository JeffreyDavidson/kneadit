<x-filament-panels::page>
    @php
        $allTenants = $this->getAllTenants();
        $comparisonData = $this->getComparisonData();
        $metrics = ['total_orders', 'month_orders', 'total_products', 'total_categories', 'avg_review', 'setup_completed', 'health_score'];
        $metricLabels = [
            'total_orders' => 'Total Orders',
            'month_orders' => 'This Month Orders',
            'total_products' => 'Total Products',
            'total_categories' => 'Total Categories',
            'avg_review' => 'Avg Review Rating',
            'setup_completed' => 'Setup Completion',
            'health_score' => 'Health Score',
        ];
    @endphp

    {{-- Tenant Selectors --}}
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
    }" style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Select Tenants to Compare</div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            @for ($i = 1; $i <= 3; $i++)
                <div style="flex: 1; min-width: 200px;">
                    <label style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; display: block; margin-bottom: 0.25rem;">Tenant {{ $i }}</label>
                    <select x-model="tenant{{ $i }}" style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); color: #faf0d6; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.875rem;">
                        <option value="">— Select —</option>
                        @foreach ($allTenants as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endfor
            <button @click="compare()" style="background: #d4920c; color: #1c1410; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; height: 38px;">
                Compare
            </button>
        </div>
    </div>

    @if (count($comparisonData) > 0)
        {{-- Side-by-side Comparison --}}
        <div style="display: grid; grid-template-columns: repeat({{ count($comparisonData) }}, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            @foreach ($comparisonData as $tenant)
                <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column;">
                    {{-- Header --}}
                    <div style="margin-bottom: 1rem; text-align: center; padding-bottom: 1rem; border-bottom: 1px solid rgba(212,146,12,0.08);">
                        <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $tenant['name'] }}</div>
                        <span style="display: inline-block; background: {{ $tenant['plan'] === 'premium' ? '#d4920c' : ($tenant['plan'] === 'pro' ? '#e8b04a' : 'rgba(212,146,12,0.1)') }}; color: {{ $tenant['plan'] === 'free' ? '#d4920c' : '#1c1410' }}; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">
                            {{ $tenant['plan'] }}
                        </span>
                    </div>

                    {{-- Metrics --}}
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
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: #2a1f18; border-radius: 8px;">
                                <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; align-self: center;">{{ $row['label'] }}</span>
                                <span style="color: #faf0d6; font-weight: 700;">{{ $row['value'] }}</span>
                            </div>
                        @endforeach
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; align-self: center;">Health Score</span>
                            <span style="color: {{ $tenant['health_score'] > 70 ? '#10b981' : ($tenant['health_score'] >= 40 ? '#f59e0b' : '#ef4444') }}; font-weight: 700;">{{ $tenant['health_score'] }}/100</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bar Chart Comparison --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Visual Comparison</div>
            @php
                $chartMetrics = ['total_orders', 'total_products', 'health_score'];
                $chartLabels = ['Total Orders', 'Total Products', 'Health Score'];
                $barColors = ['#d4920c', '#e8b04a', '#f5d88e'];
            @endphp
            @foreach ($chartMetrics as $idx => $metric)
                @php
                    $maxVal = max(array_column($comparisonData, $metric)) ?: 1;
                @endphp
                <div style="margin-bottom: 1.5rem;">
                    <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">{{ $chartLabels[$idx] }}</div>
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
        </div>
    @else
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 3rem; text-align: center;">
            <div style="margin-bottom: 1rem;">
                <svg style="width: 48px; height: 48px; display: inline-block;" viewBox="0 0 24 24" fill="none" stroke="#d4920c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div style="color: #faf0d6; font-size: 1rem;">Select 2–3 tenants above to compare their metrics side by side.</div>
        </div>
    @endif
</x-filament-panels::page>
