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
        <p class="text-cinnamon text-sm m-0">Monitor which bakers have completed their setup.</p>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-central.stat-card label="Total Tenants" value-class="text-[1.75rem] text-white">{{ $stats['total'] }}</x-central.stat-card>
        <x-central.stat-card label="Fully Onboarded" value-class="text-[1.75rem] text-emerald-500">{{ $stats['fully_onboarded'] }}</x-central.stat-card>
        <x-central.stat-card label="Needs Attention" value-class="text-[1.75rem] text-red-500">{{ $stats['needs_attention'] }}</x-central.stat-card>
    </div>

    {{-- Filters --}}
    <x-central.card class="mb-6">
        <div class="flex gap-3 flex-wrap items-end">
            <div class="flex-1 min-w-[180px]">
                <label for="filter-status" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Status</label>
                <x-central.select id="filter-status" wire:model.live="filterStatus">
                    <option value="all">All</option>
                    <option value="needs_attention">Needs attention (< 6 / 7)</option>
                    <option value="stuck">Stuck (7+ days, not onboarded)</option>
                    <option value="fully_onboarded">Fully onboarded</option>
                </x-central.select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label for="filter-plan" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Plan</label>
                <x-central.select id="filter-plan" wire:model.live="filterPlan">
                    <option value="all">All plans</option>
                    <option value="starter">Starter</option>
                    <option value="growth">Growth</option>
                    <option value="pro">Pro</option>
                    <option value="trial">Trial</option>
                </x-central.select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label for="filter-sort" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Sort</label>
                <x-central.select id="filter-sort" wire:model.live="sort">
                    <option value="progress_asc">Least progress first</option>
                    <option value="progress_desc">Most progress first</option>
                    <option value="newest">Newest signups</option>
                    <option value="oldest">Oldest signups</option>
                </x-central.select>
            </div>
            <button type="button" wire:click="resetFilters"
                class="inline-flex items-center gap-1 text-[0.75rem] text-cinnamon hover:text-honey transition-colors cursor-pointer whitespace-nowrap pb-2">
                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                Reset
            </button>
        </div>
    </x-central.card>

    {{-- Tenant Cards --}}
    @if ($tenants->isEmpty())
        <x-central.card padding="py-16 px-8" class="text-center">
            <x-heroicon-o-clipboard-document-check class="w-12 h-12 text-cinnamon mx-auto mb-4 block" />
            <div class="text-white text-lg font-semibold mb-2">No tenants match your filters</div>
            <div class="text-cinnamon text-sm">Try clearing your filters to see everyone.</div>
        </x-central.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($tenants as $tenant)
                @php
                    $pct = round(($tenant['completed'] / $tenant['total']) * 100);
                    $statusTextClass = $tenant['completed'] <= 2 ? 'text-red-400' : ($tenant['completed'] <= 5 ? 'text-amber-400' : 'text-emerald-400');
                    $statusBgClass = $tenant['completed'] <= 2 ? 'bg-red-500' : ($tenant['completed'] <= 5 ? 'bg-amber-500' : 'bg-emerald-500');
                    $cardTintClass = $tenant['completed'] <= 2 ? 'bg-red-500/5 border-red-500/20' : ($tenant['completed'] <= 5 ? 'bg-amber-500/5 border-amber-500/15' : '');
                    $planClass = match ($tenant['plan']) {
                        'pro' => 'bg-honey text-warm-black',
                        'growth' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                        'starter' => 'bg-sky-500/20 text-sky-400 border border-sky-500/30',
                        default => 'bg-honey/15 text-butter border border-honey/25',
                    };
                    $tenantUrl = \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $tenant['id']]);
                @endphp
                <x-central.card class="flex flex-col {{ $cardTintClass }}">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-3 gap-2">
                        <div class="min-w-0 flex-1">
                            <a href="{{ $tenantUrl }}" class="text-base font-bold text-white hover:text-honey transition-colors no-underline truncate block">{{ $tenant['name'] }}</a>
                            <x-central.eyebrow>{{ $tenant['subdomain'] }}.getkneadit.app</x-central.eyebrow>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] shrink-0 {{ $planClass }}">
                            {{ $tenant['plan'] }}
                        </span>
                    </div>

                    {{-- Owner + signup date --}}
                    <div class="text-[0.8rem] text-parchment mb-3">
                        {{ $tenant['owner'] }}
                        @if ($tenant['created_at'])
                            · <span class="text-cinnamon" title="{{ $tenant['created_at']->format('M j, Y · g:i A') }}">
                                Signed up {{ $tenant['created_at']->format('M j, Y') }}
                                ({{ $tenant['days_since_signup'] }}d ago)
                            </span>
                        @endif
                    </div>

                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="flex items-baseline justify-between mb-2">
                            <x-central.eyebrow as="span">Progress</x-central.eyebrow>
                            <span class="{{ $statusTextClass }} text-[0.75rem] font-bold tabular-nums">
                                {{ $tenant['completed'] }} / {{ $tenant['total'] }} · {{ $pct }}%
                            </span>
                        </div>
                        <div class="bg-espresso rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $statusBgClass }}" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>

                    {{-- Checklist --}}
                    <div class="flex flex-col gap-1.5 mb-4 flex-1">
                        @foreach ($tenant['checks'] as $key => $passed)
                            <div class="flex items-center gap-2 text-[0.8rem]">
                                @if ($passed)
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-check class="w-3 h-3 text-emerald-400" stroke-width="3" />
                                    </span>
                                    <span class="text-parchment">{{ $checkLabels[$key] }}</span>
                                @else
                                    <span class="w-5 h-5 rounded-full border border-cinnamon/30 flex-shrink-0"></span>
                                    <span class="text-cinnamon/70">{{ $checkLabels[$key] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Action --}}
                    <a href="{{ $tenantUrl }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-[0.75rem] font-semibold bg-espresso text-honey border border-honey/25 hover:border-honey hover:bg-honey/5 transition-colors no-underline">
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        View Tenant
                    </a>
                </x-central.card>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
