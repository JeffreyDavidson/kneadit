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
    }" style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Select Tenants to Compare</h3>
        <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: end;">
            @for ($i = 1; $i <= 3; $i++)
                <div style="flex: 1; min-width: 200px;">
                    <label style="color: #f5d88e; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">Tenant {{ $i }}</label>
                    <select x-model="tenant{{ $i }}" style="width: 100%; background: #2a1f18; border: 1px solid #3d2c1e; color: #faf0d6; padding: 8px 12px; border-radius: 8px; font-size: 14px;">
                        <option value="">— Select —</option>
                        @foreach ($allTenants as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endfor
            <button @click="compare()" style="background: #d4920c; color: #0c0a09; padding: 8px 24px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; height: 38px;">
                Compare
            </button>
        </div>
    </div>

    @if (count($comparisonData) > 0)
        {{-- Side-by-side Comparison --}}
        <div style="display: grid; grid-template-columns: repeat({{ count($comparisonData) }}, 1fr); gap: 20px; margin-bottom: 32px;">
            @foreach ($comparisonData as $tenant)
                <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px;">
                    {{-- Header --}}
                    <div style="margin-bottom: 20px; text-align: center;">
                        <h3 style="color: #faf0d6; font-size: 18px; font-weight: 700; margin-bottom: 8px;">{{ $tenant['name'] }}</h3>
                        <span style="background: {{ $tenant['plan'] === 'premium' ? '#d4920c' : ($tenant['plan'] === 'pro' ? '#e8b04a' : '#3d2c1e') }}; color: {{ $tenant['plan'] === 'free' ? '#f5d88e' : '#0c0a09' }}; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                            {{ $tenant['plan'] }}
                        </span>
                    </div>

                    {{-- Metrics --}}
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Total Orders</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['total_orders'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">This Month</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['month_orders'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Products</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['total_products'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Categories</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['total_categories'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Avg Review</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['avg_review'] ?: '—' }} ⭐</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Setup</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['setup_completed'] }}/7</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Days Since Signup</span>
                            <span style="color: #faf0d6; font-weight: 700;">{{ $tenant['days_since_signup'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #2a1f18; border-radius: 8px;">
                            <span style="color: #e8b04a; font-size: 13px;">Health Score</span>
                            <span style="color: {{ $tenant['health_score'] >= 60 ? '#22c55e' : ($tenant['health_score'] >= 30 ? '#e8b04a' : '#ef4444') }}; font-weight: 700;">{{ $tenant['health_score'] }}/100</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bar Chart Comparison --}}
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px;">
            <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; margin-bottom: 20px;">Visual Comparison</h3>
            @php
                $chartMetrics = ['total_orders', 'total_products', 'health_score'];
                $chartLabels = ['Total Orders', 'Total Products', 'Health Score'];
                $barColors = ['#d4920c', '#e8b04a', '#f5d88e'];
            @endphp
            @foreach ($chartMetrics as $idx => $metric)
                @php
                    $maxVal = max(array_column($comparisonData, $metric)) ?: 1;
                @endphp
                <div style="margin-bottom: 24px;">
                    <div style="color: #f5d88e; font-size: 13px; margin-bottom: 8px;">{{ $chartLabels[$idx] }}</div>
                    @foreach ($comparisonData as $tIdx => $tenant)
                        @php $pct = round(($tenant[$metric] / $maxVal) * 100); @endphp
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                            <span style="color: #faf0d6; font-size: 12px; width: 120px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tenant['name'] }}</span>
                            <div style="flex: 1; background: #2a1f18; border-radius: 4px; height: 24px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColors[$tIdx % 3] }}; border-radius: 4px; transition: width 0.3s;"></div>
                            </div>
                            <span style="color: #faf0d6; font-size: 13px; font-weight: 600; width: 50px;">{{ $tenant[$metric] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 48px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 12px;">⚖️</div>
            <p style="color: #e8b04a; font-size: 16px;">Select 2-3 tenants above to compare their metrics side by side.</p>
        </div>
    @endif
</x-filament-panels::page>
