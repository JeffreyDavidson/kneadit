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
    {{-- Subtitle --}}
    <div style="margin-bottom: 1.5rem;">
        <p style="color: #8b6844; font-size: 0.875rem; margin: 0;">Monitor which bakers have completed their setup</p>
    </div>

    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Total Tenants</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ffffff;">{{ $stats['total'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Fully Onboarded</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #10b981;">{{ $stats['fully_onboarded'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Needs Attention</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444;">{{ $stats['needs_attention'] }}</div>
        </div>
    </div>

    {{-- Tenant Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1rem;">
        @foreach($tenants as $tenant)
            @php
                $pct = round(($tenant['completed'] / $tenant['total']) * 100);
                if ($tenant['completed'] <= 2) {
                    $statusColor = '#ef4444';
                } elseif ($tenant['completed'] <= 5) {
                    $statusColor = '#f59e0b';
                } else {
                    $statusColor = '#10b981';
                }
            @endphp
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                {{-- Header --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 1rem; font-weight: 700; color: #ffffff;">{{ $tenant['name'] }}</div>
                        <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">{{ $tenant['subdomain'] }}.kneadit.app</div>
                    </div>
                    <span style="display: inline-block; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;
                        @if($tenant['plan'] === 'pro') background: #d4920c; color: #0c0a09;
                        @elseif($tenant['plan'] === 'enterprise') background: #e8b04a; color: #0c0a09;
                        @else background: rgba(212,146,12,0.15); color: #f5d88e; @endif">
                        {{ $tenant['plan'] }}
                    </span>
                </div>

                {{-- Owner --}}
                <div style="font-size: 0.8rem; color: #faf0d6; margin-bottom: 0.75rem;">
                    {{ $tenant['owner'] }} · <span style="color: #8b6844;">{{ $tenant['days_since_signup'] }} days ago</span>
                </div>

                {{-- Divider --}}
                <div style="border-top: 1px solid rgba(212,146,12,0.08); margin-bottom: 0.75rem;"></div>

                {{-- Progress Bar --}}
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Progress</span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $statusColor }};">{{ $tenant['completed'] }}/{{ $tenant['total'] }}</span>
                    </div>
                    <div style="background: rgba(212,146,12,0.08); border-radius: 9999px; height: 6px; overflow: hidden;">
                        <div style="height: 100%; border-radius: 9999px; background: {{ $statusColor }}; width: {{ $pct }}%;"></div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                    @foreach($tenant['checks'] as $key => $passed)
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                            @if($passed)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px; color: #10b981; flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span style="color: #faf0d6;">{{ $checkLabels[$key] }}</span>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px; color: #ef4444; flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                <span style="color: #8b6844;">{{ $checkLabels[$key] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
