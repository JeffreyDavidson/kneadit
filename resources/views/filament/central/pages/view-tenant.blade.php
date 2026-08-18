@php
    use Carbon\Carbon;

    $stats = $this->getTenantStats();
    $tenant = $record;

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
        $row('Subdomain', $tenant->id . '.getkneadit.app', mono: true),
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
    <x-central.card class="mb-6 flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex items-center gap-4 flex-1 min-w-0">
            <div class="shrink-0 w-14 h-14 rounded-xl bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[1.15rem]">
                {{ $initials($tenant->store_name ?: $tenant->name ?: $tenant->id) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-white text-[1.35rem] font-bold leading-tight truncate">{{ $tenant->store_name ?: $tenant->name }}</h2>
                </div>
                <a href="https://{{ $tenant->id }}.getkneadit.app" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 text-cinnamon text-[0.85rem] hover:text-honey transition-colors font-mono">
                    {{ $tenant->id }}.getkneadit.app
                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if ($tenant->is_active)
                <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 bg-red-500/15 border border-red-500/25 text-red-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Inactive
                </span>
            @endif

            @if ($planValue)
                <span class="inline-flex items-center gap-1 bg-honey/10 border border-honey/25 text-honey text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1 capitalize">
                    {{ $planValue }}
                </span>
            @endif

            @if ($tenant->free_forever)
                <span class="inline-flex items-center gap-1 bg-amber-500/15 border border-amber-500/25 text-amber-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-sparkles class="w-3 h-3" />
                    Free Forever
                </span>
            @endif

            @if ($isOnTrial)
                @php $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($trialEnd, false)); @endphp
                <span class="inline-flex items-center gap-1.5 bg-sky-500/15 border border-sky-500/25 text-sky-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-clock class="w-3 h-3" />
                    Trial · {{ $daysLeft }}d left
                </span>
            @elseif ($trialExpired)
                <span class="inline-flex items-center gap-1.5 bg-red-500/15 border border-red-500/25 text-red-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                    Trial Expired
                </span>
            @endif

            @if ($tenant->storefront_enabled)
                <span class="inline-flex items-center gap-1 bg-espresso border border-honey/15 text-parchment text-[0.7rem] font-semibold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-building-storefront class="w-3 h-3" />
                    Storefront On
                </span>
            @else
                <span class="inline-flex items-center gap-1 bg-espresso border border-cinnamon/20 text-cinnamon text-[0.7rem] font-semibold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-building-storefront class="w-3 h-3" />
                    Storefront Off
                </span>
            @endif
        </div>
    </x-central.card>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-b border-honey/12 flex items-center gap-1 overflow-x-auto">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'notes' => ['label' => 'Notes', 'icon' => 'pencil-square', 'count' => $tenant->notes()->count()],
                    'activity' => ['label' => 'Activity', 'icon' => 'clock', 'count' => $this->getTenantAuditEntries()->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-honey'
                        : 'text-cinnamon border-transparent hover:text-parchment'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 -mb-px border-b-2 text-[0.85rem] font-semibold transition-colors cursor-pointer whitespace-nowrap">
                    @switch($t['icon'])
                        @case('chart-bar-square') <x-heroicon-o-chart-bar-square class="w-4 h-4" /> @break
                        @case('pencil-square') <x-heroicon-o-pencil-square class="w-4 h-4" /> @break
                        @case('clock') <x-heroicon-o-clock class="w-4 h-4" /> @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span :class="tab === '{{ $key }}' ? 'bg-honey/15 text-honey' : 'bg-espresso text-cinnamon'"
                            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[0.7rem] font-bold transition-colors">
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Revenue</x-central.eyebrow>
                    <div class="text-white font-bold text-[1.5rem] leading-none">@money($stats['revenue'])</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Orders</x-central.eyebrow>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['orders']) }}</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Customers</x-central.eyebrow>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['customers']) }}</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Products</x-central.eyebrow>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['products']) }}</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Reviews</x-central.eyebrow>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['reviews']) }}</div>
                </x-central.card>
                <x-central.card padding="p-4">
                    <x-central.eyebrow class="mb-1">Last Order</x-central.eyebrow>
                    <div class="text-white font-semibold text-[0.95rem] leading-tight mt-0.5">
                        {{ $stats['last_order'] ? Carbon::parse($stats['last_order'])->diffForHumans() : 'Never' }}
                    </div>
                </x-central.card>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-central.card>
                    <x-central.eyebrow class="mb-4">Store &amp; Domains</x-central.eyebrow>
                    <dl class="divide-y divide-honey/8">
                        @foreach ($storeRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-cinnamon text-[0.8rem] shrink-0 pt-0.5">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-parchment' : '' }}">{{ $r['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-central.card>

                <x-central.card>
                    <x-central.eyebrow class="mb-4">Owner &amp; Account</x-central.eyebrow>
                    <dl class="divide-y divide-honey/8">
                        @foreach ($ownerRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-cinnamon text-[0.8rem] shrink-0 pt-0.5">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-parchment' : '' }}">{{ $r['value'] }}</dd>
                            </div>
                        @endforeach
                        @if ($trialEnd)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-cinnamon text-[0.8rem] shrink-0 pt-0.5">Trial Ends</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 rounded-xl border border-honey/12 bg-warm-black p-4">
                        <div class="shrink-0 w-12 h-12 rounded-lg border border-honey/20" style="background: {{ $tenant->brand_color_primary ?: '#d4920c' }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Primary</div>
                            <div class="text-white text-[0.9rem] font-semibold font-mono">{{ $tenant->brand_color_primary ?: '— not set —' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-honey/12 bg-warm-black p-4">
                        <div class="shrink-0 w-12 h-12 rounded-lg border border-honey/20" style="background: {{ $tenant->brand_color_secondary ?: '#e8b04a' }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Secondary</div>
                            <div class="text-white text-[0.9rem] font-semibold font-mono">{{ $tenant->brand_color_secondary ?: '— not set —' }}</div>
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
                    <x-central.textarea wire:model="noteBody" rows="3" placeholder="What happened? What's worth remembering about this tenant?" />
                    @error('noteBody')
                        <p class="text-red-400 text-[0.8rem] mt-1.5">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-cinnamon text-[0.75rem]">Notes are visible to all platform admins.</span>
                        <x-central.button type="submit" class="gap-1.5">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" stroke-width="2.5" />
                            Save Note
                        </x-central.button>
                    </div>
                </form>
            </x-central.card>

            {{-- List of notes --}}
            <x-central.card>
                <div class="flex items-center justify-between mb-4">
                    <x-central.eyebrow>Notes</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]">{{ $tenant->notes->count() }} total</span>
                </div>

                @php $notes = $tenant->notes->sortByDesc('created_at'); @endphp

                @if ($notes->isEmpty())
                    <div class="text-center py-10">
                        <x-heroicon-o-pencil-square class="w-10 h-10 text-cinnamon/40 mx-auto mb-3" />
                        <div class="text-parchment text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-cinnamon text-[0.8rem] mt-1">Add context about this tenant so your team can pick up where you left off.</div>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($notes as $note)
                            <li class="rounded-xl border border-honey/12 bg-warm-black p-4" wire:key="note-{{ $note->id }}">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <div class="w-7 h-7 rounded-full bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[0.7rem]">
                                            {{ strtoupper(substr($note->author, 0, 2)) }}
                                        </div>
                                        <span class="text-white font-semibold text-[0.85rem]">{{ $note->author }}</span>
                                        <span class="text-cinnamon text-[0.75rem]">{{ $note->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <button type="button" wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="inline-flex items-center gap-1 text-cinnamon hover:text-red-400 text-[0.75rem] transition-colors cursor-pointer">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">{{ $note->body }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-central.card>
        </div>

        {{-- ============== TAB: ACTIVITY ============== --}}
        <div x-show="tab === 'activity'" x-cloak>
            <x-central.card>
                <div class="flex items-center justify-between mb-4">
                    <x-central.eyebrow>Admin Audit Log</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.7rem]">Actions platform admins have taken on this tenant</span>
                </div>

                @php $entries = $this->getTenantAuditEntries(); @endphp

                @if ($entries->isEmpty())
                    <div class="text-center py-12">
                        <x-heroicon-o-clock class="w-10 h-10 text-cinnamon/40 mx-auto mb-3" />
                        <div class="text-parchment text-[0.9rem] font-semibold">No admin activity yet</div>
                        <div class="text-cinnamon text-[0.8rem] mt-1">Platform actions on this tenant (impersonation, plan changes, etc.) will appear here.</div>
                    </div>
                @else
                    <ol class="relative border-l border-honey/12 ml-2">
                        @foreach ($entries as $entry)
                            <li class="ml-6 mb-6 last:mb-0">
                                <span class="absolute -left-1.5 w-3 h-3 rounded-full bg-honey border-2 border-warm-black"></span>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[0.65rem] uppercase tracking-[0.1em] text-honey font-bold mb-0.5">{{ $entry->action }}</div>
                                        <div class="text-white text-[0.9rem] font-semibold">{{ $entry->description }}</div>
                                        @if ($entry->ip_address)
                                            <div class="text-cinnamon text-[0.7rem] font-mono mt-1">from {{ $entry->ip_address }}</div>
                                        @endif
                                    </div>
                                    <div class="text-cinnamon text-[0.75rem] whitespace-nowrap">{{ $entry->created_at?->diffForHumans() }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
