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
    <div class="mb-6">
        <p class="text-cinnamon text-sm m-0">Monitor which bakers have completed their setup</p>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-central.stat-card label="Total Tenants" value-class="text-[1.75rem] text-white">{{ $stats['total'] }}</x-central.stat-card>
        <x-central.stat-card label="Fully Onboarded" value-class="text-[1.75rem] text-emerald-500">{{ $stats['fully_onboarded'] }}</x-central.stat-card>
        <x-central.stat-card label="Needs Attention" value-class="text-[1.75rem] text-red-500">{{ $stats['needs_attention'] }}</x-central.stat-card>
    </div>

    {{-- Tenant Cards --}}
    <div class="grid grid-cols-[repeat(auto-fill,minmax(380px,1fr))] gap-4">
        @foreach ($tenants as $tenant)
            @php
                $pct = round(($tenant['completed'] / $tenant['total']) * 100);
                $statusTextClass = $tenant['completed'] <= 2 ? 'text-red-500' : ($tenant['completed'] <= 5 ? 'text-amber-500' : 'text-emerald-500');
                $statusBgClass = $tenant['completed'] <= 2 ? 'bg-red-500' : ($tenant['completed'] <= 5 ? 'bg-amber-500' : 'bg-emerald-500');
                $planClass = match ($tenant['plan']) {
                    'pro' => 'bg-honey text-warm-black',
                    'enterprise' => 'bg-golden text-warm-black',
                    default => 'bg-honey/15 text-butter',
                };
            @endphp
            <x-central.card>
                {{-- Header --}}
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="text-base font-bold text-white">{{ $tenant['name'] }}</div>
                        <x-central.eyebrow>{{ $tenant['subdomain'] }}.kneadit.app</x-central.eyebrow>
                    </div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] {{ $planClass }}">
                        {{ $tenant['plan'] }}
                    </span>
                </div>

                {{-- Owner --}}
                <div class="text-[0.8rem] text-parchment mb-3">
                    {{ $tenant['owner'] }} · <span class="text-cinnamon">{{ $tenant['days_since_signup'] }} days ago</span>
                </div>

                {{-- Divider --}}
                <div class="border-t border-honey/8 mb-3"></div>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="flex justify-between mb-1">
                        <x-central.eyebrow as="span">Progress</x-central.eyebrow>
                        <span class="text-xs font-bold {{ $statusTextClass }}">{{ $tenant['completed'] }}/{{ $tenant['total'] }}</span>
                    </div>
                    <div class="bg-honey/8 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full {{ $statusBgClass }}" style="width: {{ $pct }}%;"></div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div class="flex flex-col gap-1.5">
                    @foreach ($tenant['checks'] as $key => $passed)
                        <div class="flex items-center gap-2 text-[0.8rem]">
                            @if ($passed)
                                <x-heroicon-o-check class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" />
                                <span class="text-parchment">{{ $checkLabels[$key] }}</span>
                            @else
                                <x-heroicon-o-x-mark class="w-3.5 h-3.5 text-red-500 flex-shrink-0" />
                                <span class="text-cinnamon">{{ $checkLabels[$key] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-central.card>
        @endforeach
    </div>
</x-filament-panels::page>
