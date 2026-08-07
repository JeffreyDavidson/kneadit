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
    {{-- Page subheading is rendered by Filament from $subheading on the page class.
         Convention for these pages: mb-6 between top-level sections (stats, filters,
         main grid), gap-4 within grids of cards, gap-3 within filter rows. --}}

    {{-- Summary Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-central.stat-card label="Total Tenants" value-class="text-[1.75rem] text-white">
            {{ $stats['total'] }}</x-central.stat-card>
        <x-central.stat-card label="Fully Onboarded" value-class="text-[1.75rem] text-emerald-500">
            {{ $stats['fully_onboarded'] }}</x-central.stat-card>
        <x-central.stat-card label="Needs Attention" value-class="text-[1.75rem] text-red-500">
            {{ $stats['needs_attention'] }}</x-central.stat-card>
    </div>

    {{-- Filters --}}
    <x-central.card class="mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[180px] flex-1">
                <label
                    for="filter-status"
                    class="text-cinnamon mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] uppercase"
                >Status</label>
                <x-central.select id="filter-status" wire:model.live="filterStatus">
                    <option value="all">All</option>
                    <option value="needs_attention">Needs attention (< 6 / 7)</option>
                    <option value="stuck">Stuck (7+ days, not onboarded)</option>
                    <option value="fully_onboarded">Fully onboarded</option>
                </x-central.select>
            </div>
            <div class="min-w-[160px] flex-1">
                <label
                    for="filter-plan"
                    class="text-cinnamon mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] uppercase"
                >Plan</label>
                <x-central.select id="filter-plan" wire:model.live="filterPlan">
                    <option value="all">All plans</option>
                    <option value="starter">Starter</option>
                    <option value="growth">Growth</option>
                    <option value="pro">Pro</option>
                    <option value="trial">Trial</option>
                </x-central.select>
            </div>
            <div class="min-w-[180px] flex-1">
                <label
                    for="filter-sort"
                    class="text-cinnamon mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] uppercase"
                >Sort</label>
                <x-central.select id="filter-sort" wire:model.live="sort">
                    <option value="progress_asc">Least progress first</option>
                    <option value="progress_desc">Most progress first</option>
                    <option value="newest">Newest signups</option>
                    <option value="oldest">Oldest signups</option>
                </x-central.select>
            </div>
            <button
                type="button"
                wire:click="resetFilters"
                class="text-cinnamon hover:text-honey inline-flex cursor-pointer items-center gap-1 pb-2 text-[0.75rem] whitespace-nowrap transition-colors"
            >
                <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                Reset
            </button>
        </div>
    </x-central.card>

    {{-- Tenant Cards --}}
    @if ($tenants->isEmpty())
        <x-central.card padding="py-16 px-8" class="text-center">
            <x-heroicon-o-clipboard-document-check class="text-cinnamon mx-auto mb-4 block h-12 w-12" />
            <div class="mb-2 text-lg font-semibold text-white">No tenants match your filters</div>
            <div class="text-cinnamon text-sm">Try clearing your filters to see everyone.</div>
        </x-central.card>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <a
                                href="{{ $tenantUrl }}"
                                class="hover:text-honey block truncate text-base font-bold text-white no-underline transition-colors"
                            >{{ $tenant['name'] }}</a>
                            <x-central.eyebrow>{{ $tenant['subdomain'] }}.getkneadit.app</x-central.eyebrow>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] shrink-0 {{ $planClass }}">
                            {{ $tenant['plan'] }}
                        </span>
                    </div>

                    {{-- Owner + signup date --}}
                    <div class="text-parchment mb-3 text-[0.8rem]">
                        {{ $tenant['owner'] }}
                        @if ($tenant['created_at'])
                            ·
                            <span class="text-cinnamon" title="{{ $tenant['created_at']->format('M j, Y · g:i A') }}">
                                Signed up {{ $tenant['created_at']->format('M j, Y') }} ({{ $tenant['days_since_signup'] }}d
                                ago)
                            </span>
                        @endif
                    </div>

                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="mb-2 flex items-baseline justify-between">
                            <x-central.eyebrow as="span">Progress</x-central.eyebrow>
                            <span class="{{ $statusTextClass }} text-[0.75rem] font-bold tabular-nums">
                                {{ $tenant['completed'] }} / {{ $tenant['total'] }} · {{ $pct }}%
                            </span>
                        </div>
                        <div class="bg-espresso h-2 overflow-hidden rounded-full">
                            <div
                                class="h-full rounded-full transition-all {{ $statusBgClass }}"
                                style="width: {{ $pct }}%;"
                            ></div>
                        </div>
                    </div>

                    {{-- Checklist --}}
                    <div class="mb-4 flex flex-1 flex-col gap-1.5">
                        @foreach ($tenant['checks'] as $key => $passed)
                            <div class="flex items-center gap-2 text-[0.8rem]">
                                @if ($passed)
                                    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/15">
                                        <x-heroicon-o-check class="h-3 w-3 text-emerald-400" stroke-width="3" />
                                    </span>
                                    <span class="text-parchment">{{ $checkLabels[$key] }}</span>
                                @else
                                    <span class="border-cinnamon/30 h-5 w-5 flex-shrink-0 rounded-full border"></span>
                                    <span class="text-cinnamon/70">{{ $checkLabels[$key] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Action --}}
                    <a
                        href="{{ $tenantUrl }}"
                        class="bg-espresso text-honey border-honey/25 hover:border-honey hover:bg-honey/5 inline-flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-[0.75rem] font-semibold no-underline transition-colors"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                        View Tenant
                    </a>
                </x-central.card>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
