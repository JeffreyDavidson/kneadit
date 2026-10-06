@inject('tenantUrls', 'App\Services\Tenants\TenantUrlGenerator')

@php
    use App\DataTransferObjects\Settings\BrandingSettings;
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

        return strtoupper(substr($parts[0] ?? '?', 0, 1).substr($parts[1] ?? '', 0, 1));
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
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[1.15rem] font-bold text-(--kn-honey-text)">
                {{ $initials($tenant->store_name ?: $tenant->name ?: $tenant->id) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2">
                    <h2 class="truncate text-[1.35rem] leading-tight font-bold text-(--kn-ink)">
                        {{ $tenant->store_name ?: $tenant->name }}
                    </h2>
                </div>
                <a
                    href="{{ $storefrontUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 font-mono text-[0.85rem] text-(--kn-muted) transition-colors hover:text-(--kn-honey-text)"
                >
                    {{ $storefrontHost }}
                    <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                </a>
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex flex-wrap items-center gap-2">
            @if ($tenant->is_active)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-success)/25 bg-(--kn-success-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-success) uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-(--kn-success)"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-danger)/25 bg-(--kn-danger-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-danger) uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-(--kn-danger)"></span>
                    Inactive
                </span>
            @endif

            @if ($planValue)
                <span class="inline-flex items-center gap-1 rounded-full border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-honey-text) capitalize uppercase">
                    {{ $planValue }}
                </span>
            @endif

            @if ($tenant->free_forever)
                <span class="inline-flex items-center gap-1 rounded-full border border-(--kn-warning)/25 bg-(--kn-warning-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-warning) uppercase">
                    <x-heroicon-o-sparkles class="h-3 w-3" />
                    Free Forever
                </span>
            @endif

            @if ($isOnTrial)
                @php $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($trialEnd, false)); @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-info)/25 bg-(--kn-info-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-info) uppercase">
                    <x-heroicon-o-clock class="h-3 w-3" />
                    Trial · {{ $daysLeft }}d left
                </span>
            @elseif ($trialExpired)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-danger)/25 bg-(--kn-danger-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-danger) uppercase">
                    <x-heroicon-o-exclamation-triangle class="h-3 w-3" />
                    Trial Expired
                </span>
            @endif

            @if ($tenant->is_paused)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-danger)/25 bg-(--kn-danger-tint) px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-(--kn-danger) uppercase">
                    <x-heroicon-o-pause-circle class="h-3 w-3" />
                    Paused
                </span>
            @endif

            @if ($tenant->storefront_enabled)
                <span class="inline-flex items-center gap-1 rounded-full border border-(--kn-border) bg-(--kn-surface-sunken) px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-ink) uppercase">
                    <x-heroicon-o-building-storefront class="h-3 w-3" />
                    Storefront On
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full border border-(--kn-border) bg-(--kn-surface-sunken) px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
                    <x-heroicon-o-building-storefront class="h-3 w-3" />
                    Storefront Off
                </span>
            @endif
        </div>
    </x-central.card>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="flex items-center gap-1 overflow-x-auto border-b border-(--kn-border)">
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
                        ? 'text-(--kn-ink) border-(--kn-honey)'
                        : 'text-(--kn-muted) border-transparent hover:text-(--kn-ink)'"
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
                            :class="tab === '{{ $key }}' ? 'bg-(--kn-warning-tint) text-(--kn-honey-text)' : 'bg-(--kn-surface-sunken) text-(--kn-muted)'"
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
                    <div class="text-[1.5rem] leading-none font-bold text-(--kn-ink)">@money($stats['revenue'])</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Orders</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-(--kn-ink)">
                        {{ number_format($stats['orders']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Customers</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-(--kn-ink)">
                        {{ number_format($stats['customers']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Products</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-(--kn-ink)">
                        {{ number_format($stats['products']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Reviews</x-central.eyebrow>
                    <div class="text-[1.5rem] leading-none font-bold text-(--kn-ink)">
                        {{ number_format($stats['reviews']) }}
                    </div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Last Order</x-central.eyebrow>
                    <div class="mt-0.5 text-[0.95rem] leading-tight font-semibold text-(--kn-ink)">
                        {{ $stats['last_order'] ? Carbon::parse($stats['last_order'])->diffForHumans() : 'Never' }}
                    </div>
                </x-central.card>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-central.card>
                    <x-central.eyebrow class="mb-4">Store &amp; Domains</x-central.eyebrow>
                    <dl class="divide-y divide-(--kn-border)">
                        @foreach ($storeRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="shrink-0 pt-0.5 text-[0.8rem] text-(--kn-muted)">{{ $r['label'] }}</dt>
                                <dd class="text-(--kn-ink) text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-(--kn-ink)' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </x-central.card>

                <x-central.card>
                    <x-central.eyebrow class="mb-4">Owner &amp; Account</x-central.eyebrow>
                    <dl class="divide-y divide-(--kn-border)">
                        @foreach ($ownerRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="shrink-0 pt-0.5 text-[0.8rem] text-(--kn-muted)">{{ $r['label'] }}</dt>
                                <dd class="text-(--kn-ink) text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-(--kn-ink)' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                        @if ($trialEnd)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="shrink-0 pt-0.5 text-[0.8rem] text-(--kn-muted)">Trial Ends</dt>
                                <dd class="text-right text-[0.85rem] font-semibold text-(--kn-ink)">
                                    {{ $trialEnd->format('M j, Y') }}
                                    <span class="{{ $isOnTrial ? 'text-(--kn-info)' : 'text-(--kn-danger)' }} font-normal ml-1">({{ $trialEnd->diffForHumans() }})</span>
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
                    <div class="flex items-center gap-3 rounded-xl border border-(--kn-border) bg-(--kn-surface) p-4">
                        <div
                            class="h-12 w-12 shrink-0 rounded-lg border border-(--kn-border)"
                            style="background: {{ BrandingSettings::safeColor($tenant->brand_color_primary) }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 text-[0.7rem] font-semibold tracking-[0.1em] text-(--kn-muted) uppercase">
                                Primary
                            </div>
                            <div class="font-mono text-[0.9rem] font-semibold text-(--kn-ink)">
                                {{ $tenant->brand_color_primary ?: '— not set —' }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-(--kn-border) bg-(--kn-surface) p-4">
                        <div
                            class="h-12 w-12 shrink-0 rounded-lg border border-(--kn-border)"
                            style="background: {{ BrandingSettings::safeColor($tenant->brand_color_secondary, 'var(--kn-honey)') }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 text-[0.7rem] font-semibold tracking-[0.1em] text-(--kn-muted) uppercase">
                                Secondary
                            </div>
                            <div class="font-mono text-[0.9rem] font-semibold text-(--kn-ink)">
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
                        <p class="mt-1.5 text-[0.8rem] text-(--kn-danger)">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-[0.75rem] text-(--kn-muted)">Notes are visible to all platform admins.</span>
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
                    <span class="text-[0.75rem] text-(--kn-muted)">{{ $tenant->notes->count() }} total</span>
                </div>

                @php $notes = $tenant->notes->sortByDesc('created_at'); @endphp

                @if ($notes->isEmpty())
                    <div class="py-10 text-center">
                        <x-heroicon-o-pencil-square class="mx-auto mb-3 h-10 w-10 text-(--kn-muted)" />
                        <div class="text-[0.9rem] font-semibold text-(--kn-ink)">No notes yet</div>
                        <div class="mt-1 text-[0.8rem] text-(--kn-muted)">
                            Add context about this tenant so your team can pick up where you left off.
                        </div>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($notes as $note)
                            <li
                                class="rounded-xl border border-(--kn-border) bg-(--kn-surface) p-4"
                                wire:key="note-{{ $note->id }}"
                            >
                                <div class="mb-2 flex items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[0.7rem] font-bold text-(--kn-honey-text)">
                                            {{ strtoupper(substr($note->author, 0, 2)) }}
                                        </div>
                                        <span class="text-[0.85rem] font-semibold text-(--kn-ink)">{{ $note->author }}</span>
                                        <span class="text-[0.75rem] text-(--kn-muted)">{{ $note->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="inline-flex cursor-pointer items-center gap-1 text-[0.75rem] text-(--kn-muted) transition-colors hover:text-(--kn-danger)"
                                    >
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="text-[0.9rem] leading-relaxed whitespace-pre-wrap text-(--kn-ink)">
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
                    <span class="text-[0.7rem] text-(--kn-muted)">Actions platform admins have taken on this tenant</span>
                </div>

                @php $entries = $this->getTenantAuditEntries(); @endphp

                @if ($entries->isEmpty())
                    <div class="py-12 text-center">
                        <x-heroicon-o-clock class="mx-auto mb-3 h-10 w-10 text-(--kn-muted)" />
                        <div class="text-[0.9rem] font-semibold text-(--kn-ink)">No admin activity yet</div>
                        <div class="mt-1 text-[0.8rem] text-(--kn-muted)">
                            Platform actions on this tenant (impersonation, plan changes, etc.) will appear here.
                        </div>
                    </div>
                @else
                    <ol class="relative ml-2 border-l border-(--kn-border)">
                        @foreach ($entries as $entry)
                            <li class="mb-6 ml-6 last:mb-0">
                                <span class="absolute -left-1.5 h-3 w-3 rounded-full border-2 border-(--kn-surface) bg-(--kn-honey)"></span>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="mb-0.5 text-[0.65rem] font-bold tracking-[0.1em] text-(--kn-honey-text) uppercase">
                                            {{ $entry->action }}
                                        </div>
                                        <div class="text-[0.9rem] font-semibold text-(--kn-ink)">
                                            {{ $entry->description }}
                                        </div>
                                        @if ($entry->ip_address)
                                            <div class="mt-1 font-mono text-[0.7rem] text-(--kn-muted)">
                                                from {{ $entry->ip_address }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-[0.75rem] whitespace-nowrap text-(--kn-muted)">
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
