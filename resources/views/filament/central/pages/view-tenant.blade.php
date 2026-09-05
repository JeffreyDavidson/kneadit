@inject('tenantUrls', 'App\Services\Tenants\TenantUrlGenerator')

@php
    use Carbon\Carbon;

    $stats = $this->getTenantStats();
    $tenant = $record;
    $storefrontUrl = $tenantUrls->storefront($tenant);
    $storefrontHost = $tenantUrls->storefrontHost($tenant);

    $planValue = $tenant->plan instanceof \BackedEnum ? $tenant->plan->value : ($tenant->plan ?? null);
    $trialEnd = $tenant->trial_ends_at;
    $isOnTrial = $trialEnd && $trialEnd->isFuture();
    $trialExpired = $trialEnd && $trialEnd->isPast();

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(substr($parts[0] ?? '?', 0, 1) . substr($parts[1] ?? '', 0, 1));
    };

    $row = function (string $label, ?string $value, bool $mono = false) {
        return ['label' => $label, 'value' => $value, 'mono' => $mono];
    };

    $storeRows = [
        $row('Bakery Name', $tenant->store_name ?: '—'),
        $row('Subdomain', $storefrontHost, mono: true),
        $row('Custom Domain', $tenant->custom_domain ?: '—', mono: (bool) $tenant->custom_domain),
        $row('External Website', $tenant->external_website ?: '—'),
    ];

    $ownerRows = [
        $row('Owner', $tenant->name ?: '—'),
        $row('Email', $tenant->email ?: '—'),
        $row('Created', $tenant->created_at?->format('M j, Y')),
        $row('Last Login', $tenant->last_login_at?->diffForHumans() ?: 'Never'),
    ];
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <x-central.card class="mb-6 flex flex-col gap-5 md:flex-row md:items-center">
        <div class="flex min-w-0 flex-1 items-center gap-4">
            <div class="bg-honey/15 border-honey/25 text-honey flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border text-[1.15rem] font-bold">
                {{ $initials($tenant->store_name ?: $tenant->name ?: $tenant->id) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2">
                    <h2 class="truncate text-[1.35rem] leading-tight font-bold text-white">
                        {{ $tenant->store_name ?: $tenant->name }}
                    </h2>
                </div>
                <a
                    href="{{ $storefrontUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="text-cinnamon hover:text-honey inline-flex items-center gap-1.5 font-mono text-[0.85rem] transition-colors"
                >
                    {{ $storefrontHost }}
                    <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                </a>
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex flex-wrap items-center gap-2">
            @if ($tenant->is_active)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-emerald-400 uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/25 bg-red-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-red-400 uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    Inactive
                </span>
            @endif

            @if ($planValue)
                <span class="bg-honey/10 border-honey/25 text-honey inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] capitalize uppercase">
                    {{ $planValue }}
                </span>
            @endif

            @if ($tenant->free_forever)
                <span class="inline-flex items-center gap-1 rounded-full border border-amber-500/25 bg-amber-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-amber-400 uppercase">
                    <x-heroicon-o-sparkles class="h-3 w-3" />
                    Free Forever
                </span>
            @endif

            @if ($isOnTrial)
                @php $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($trialEnd, false)); @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-500/25 bg-sky-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-sky-400 uppercase">
                    <x-heroicon-o-clock class="h-3 w-3" />
                    Trial · {{ $daysLeft }}d left
                </span>
            @elseif ($trialExpired)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/25 bg-red-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-red-400 uppercase">
                    <x-heroicon-o-exclamation-triangle class="h-3 w-3" />
                    Trial Expired
                </span>
            @endif

            @if ($tenant->storefront_enabled)
                <span class="bg-espresso border-honey/15 text-parchment inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">
                    <x-heroicon-o-building-storefront class="h-3 w-3" />
                    Storefront On
                </span>
            @else
                <span class="bg-espresso border-cinnamon/20 text-cinnamon inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">
                    <x-heroicon-o-building-storefront class="h-3 w-3" />
                    Storefront Off
                </span>
            @endif
        </div>
    </x-central.card>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-honey/12 flex items-center gap-1 overflow-x-auto border-b">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'notes' => ['label' => 'Notes', 'icon' => 'pencil-square', 'count' => $tenant->notes()->count()],
                    'activity' => ['label' => 'Activity', 'icon' => 'clock', 'count' => $this->getTenantAuditEntries()->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button
                    type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-honey'
                        : 'text-cinnamon border-transparent hover:text-parchment'"
                    class="-mb-px inline-flex cursor-pointer items-center gap-2 border-b-2 px-4 py-2.5 text-[0.85rem] font-semibold whitespace-nowrap transition-colors"
                >
                    @switch ($t['icon'])
                        @case ('chart-bar-square')
                            <x-heroicon-o-chart-bar-square class="h-4 w-4" />
                            @break
                        @case ('pencil-square')
                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                            @break
                        @case ('clock')
                            <x-heroicon-o-clock class="h-4 w-4" />
                            @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span
                            :class="tab === '{{ $key }}' ? 'bg-honey/15 text-honey' : 'bg-espresso text-cinnamon'"
                            class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[0.7rem] font-bold transition-colors"
                        >
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Revenue</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-white">@money($stats['revenue'])</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Orders</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['orders']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Customers</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['customers']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Products</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['products']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Reviews</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['reviews']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Last Order</x-central.eyebrow>
                    <div class="mt-0.5 text-[0.95rem] leading-tight font-semibold text-white">
                        {{ $stats['last_order'] ? Carbon::parse($stats['last_order'])->diffForHumans() : 'Never' }}
                    </div>
                </x-central.card>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-central.card>
                    <x-central.eyebrow class="mb-4">Store &amp; Domains</x-central.eyebrow>
                    <dl class="divide-honey/8 divide-y">
                        @foreach ($storeRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-cinnamon shrink-0 pt-0.5 text-[0.8rem]">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-parchment' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </x-central.card>

                <x-central.card>
                    <x-central.eyebrow class="mb-4">Owner &amp; Account</x-central.eyebrow>
                    <dl class="divide-honey/8 divide-y">
                        @foreach ($ownerRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-cinnamon shrink-0 pt-0.5 text-[0.8rem]">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-parchment' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                        @if ($trialEnd)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-cinnamon shrink-0 pt-0.5 text-[0.8rem]">Trial Ends</dt>
                                <dd class="text-right text-[0.85rem] font-semibold text-white">
                                    {{ $trialEnd->format('M j, Y') }}
                                    <span class="{{ $isOnTrial ? 'text-sky-400' : 'text-red-400' }} font-normal ml-1">({{ $trialEnd->diffForHumans() }})</span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-central.card>
            </div>

            {{-- Branding --}}
            <x-central.card>
                <x-central.eyebrow class="mb-4">Branding</x-central.eyebrow>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="border-honey/12 bg-warm-black flex items-center gap-3 rounded-xl border p-4">
                        <div
                            class="border-honey/20 h-12 w-12 shrink-0 rounded-lg border"
                            style="background: {{ $tenant->brand_color_primary ?: '#d4920c' }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <div class="text-cinnamon mb-0.5 text-[0.7rem] font-semibold tracking-[0.1em] uppercase">
                                Primary
                            </div>
                            <div class="font-mono text-[0.9rem] font-semibold text-white">
                                {{ $tenant->brand_color_primary ?: '— not set —' }}
                            </div>
                        </div>
                    </div>
                    <div class="border-honey/12 bg-warm-black flex items-center gap-3 rounded-xl border p-4">
                        <div
                            class="border-honey/20 h-12 w-12 shrink-0 rounded-lg border"
                            style="background: {{ $tenant->brand_color_secondary ?: '#e8b04a' }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <div class="text-cinnamon mb-0.5 text-[0.7rem] font-semibold tracking-[0.1em] uppercase">
                                Secondary
                            </div>
                            <div class="font-mono text-[0.9rem] font-semibold text-white">
                                {{ $tenant->brand_color_secondary ?: '— not set —' }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-central.card>
        </div>

        {{-- ============== TAB: NOTES ============== --}}
        <div x-show="tab === 'notes'" x-cloak class="space-y-6">
            {{-- Add note form --}}
            <x-central.card>
                <x-central.eyebrow class="mb-3">Add Note</x-central.eyebrow>
                <form wire:submit="addNote">
                    <x-central.textarea
                        wire:model="noteBody"
                        rows="3"
                        placeholder="What happened? What's worth remembering about this tenant?"
                    />
                    @error('noteBody')
                        <p class="mt-1.5 text-[0.8rem] text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-cinnamon text-[0.75rem]">Notes are visible to all platform admins.</span>
                        <x-central.button type="submit" class="gap-1.5">
                            <x-heroicon-o-plus class="h-3.5 w-3.5" stroke-width="2.5" />
                            Save Note
                        </x-central.button>
                    </div>
                </form>
            </x-central.card>

            {{-- List of notes --}}
            <x-central.card>
                <div class="mb-4 flex items-center justify-between">
                    <x-central.eyebrow>Notes</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]">{{ $tenant->notes->count() }} total</span>
                </div>

                @php $notes = $tenant->notes->sortByDesc('created_at'); @endphp

                @if ($notes->isEmpty())
                    <div class="py-10 text-center">
                        <x-heroicon-o-pencil-square class="text-cinnamon/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-parchment text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-cinnamon mt-1 text-[0.8rem]">
                            Add context about this tenant so your team can pick up where you left off.
                        </div>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($notes as $note)
                            <li
                                class="border-honey/12 bg-warm-black rounded-xl border p-4"
                                wire:key="note-{{ $note->id }}"
                            >
                                <div class="mb-2 flex items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="bg-honey/15 border-honey/25 text-honey flex h-7 w-7 items-center justify-center rounded-full border text-[0.7rem] font-bold">
                                            {{ strtoupper(substr($note->author, 0, 2)) }}
                                        </div>
                                        <span class="text-[0.85rem] font-semibold text-white">{{ $note->author }}</span>
                                        <span class="text-cinnamon text-[0.75rem]">{{ $note->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="text-cinnamon inline-flex cursor-pointer items-center gap-1 text-[0.75rem] transition-colors hover:text-red-400"
                                    >
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">
                                    {{ $note->body }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-central.card>
        </div>

        {{-- ============== TAB: ACTIVITY ============== --}}
        <div x-show="tab === 'activity'" x-cloak>
            <x-central.card>
                <div class="mb-4 flex items-center justify-between">
                    <x-central.eyebrow>Admin Audit Log</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.7rem]">Actions platform admins have taken on this tenant</span>
                </div>

                @php $entries = $this->getTenantAuditEntries(); @endphp

                @if ($entries->isEmpty())
                    <div class="py-12 text-center">
                        <x-heroicon-o-clock class="text-cinnamon/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-parchment text-[0.9rem] font-semibold">No admin activity yet</div>
                        <div class="text-cinnamon mt-1 text-[0.8rem]">
                            Platform actions on this tenant (impersonation, plan changes, etc.) will appear here.
                        </div>
                    </div>
                @else
                    <ol class="border-honey/12 relative ml-2 border-l">
                        @foreach ($entries as $entry)
                            <li class="mb-6 ml-6 last:mb-0">
                                <span class="bg-honey border-warm-black absolute -left-1.5 h-3 w-3 rounded-full border-2"></span>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-honey mb-0.5 text-[0.65rem] font-bold tracking-[0.1em] uppercase">
                                            {{ $entry->action }}
                                        </div>
                                        <div class="text-[0.9rem] font-semibold text-white">
                                            {{ $entry->description }}
                                        </div>
                                        @if ($entry->ip_address)
                                            <div class="text-cinnamon mt-1 font-mono text-[0.7rem]">
                                                from {{ $entry->ip_address }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-cinnamon text-[0.75rem] whitespace-nowrap">
                                        {{ $entry->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
