@php
    $tenants = $this->getTenantOnboardingData();
    $stats = $this->getSummaryStats();

    $checkLabels = [
        'store_name' => 'Set store name',
        'store_logo' => 'Uploaded logo',
        'storefront_enabled' => 'Enabled storefront',
        'brand_customized' => 'Customized branding',
        'has_products' => 'Added products',
        'has_categories' => 'Added categories',
        'has_orders' => 'Received first order',
    ];
@endphp

<x-filament-panels::page>
    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <p style="color: #e8b04a; font-size: 0.9rem; margin: 0;">Monitor which bakers have completed their setup</p>
    </div>

    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px;">
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: #faf0d6;">{{ $stats['total'] }}</div>
            <div style="font-size: 0.8rem; color: #e8b04a; text-transform: uppercase; letter-spacing: 0.05em;">Total Tenants</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #10b981; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: #10b981;">{{ $stats['fully_onboarded'] }}</div>
            <div style="font-size: 0.8rem; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">Fully Onboarded</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: #ef4444;">{{ $stats['needs_attention'] }}</div>
            <div style="font-size: 0.8rem; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em;">Needs Attention</div>
        </div>
    </div>

    {{-- Tenant Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 20px;">
        @foreach($tenants as $tenant)
            @php
                $pct = round(($tenant['completed'] / $tenant['total']) * 100);
                if ($tenant['completed'] <= 2) {
                    $borderColor = '#ef4444';
                    $barColor = '#ef4444';
                } elseif ($tenant['completed'] <= 5) {
                    $borderColor = '#e8b04a';
                    $barColor = '#e8b04a';
                } else {
                    $borderColor = '#10b981';
                    $barColor = '#10b981';
                }
            @endphp
            <div style="background: #1c1410; border: 2px solid {{ $borderColor }}; border-radius: 12px; padding: 20px; transition: transform 0.15s; position: relative;">
                {{-- Header --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 600; color: #faf0d6;">{{ $tenant['name'] }}</div>
                        <div style="font-size: 0.75rem; color: #d4920c;">{{ $tenant['subdomain'] }}.kneadit.app</div>
                    </div>
                    <span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
                        @if($tenant['plan'] === 'pro') background: #d4920c; color: #0c0a09;
                        @elseif($tenant['plan'] === 'enterprise') background: #e8b04a; color: #0c0a09;
                        @else background: #2a1f18; color: #e8b04a; @endif">
                        {{ $tenant['plan'] }}
                    </span>
                </div>

                {{-- Owner --}}
                <div style="font-size: 0.8rem; color: #f5d88e; margin-bottom: 12px;">
                    {{ $tenant['owner'] }} · {{ $tenant['days_since_signup'] }} days ago
                </div>

                {{-- Progress Bar --}}
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-size: 0.75rem; color: #e8b04a;">Progress</span>
                        <span style="font-size: 0.75rem; font-weight: 600; color: {{ $borderColor }};">{{ $tenant['completed'] }}/{{ $tenant['total'] }}</span>
                    </div>
                    <div style="background: #2a1f18; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="height: 100%; border-radius: 9999px; background: {{ $barColor }}; width: {{ $pct }}%; transition: width 0.3s;"></div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($tenant['checks'] as $key => $passed)
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem;">
                            @if($passed)
                                <span style="color: #10b981; font-size: 1rem;">✓</span>
                                <span style="color: #f5d88e;">{{ $checkLabels[$key] }}</span>
                            @else
                                <span style="color: #ef4444; font-size: 1rem;">✗</span>
                                <span style="color: #ef4444; opacity: 0.7;">{{ $checkLabels[$key] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
