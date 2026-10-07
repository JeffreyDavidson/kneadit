<x-filament-panels::page>
    {{-- Tab Switcher: only shown while there is more than one tab. --}}
    @if ($this->hasPlatformEventsTab())
        <div class="mb-6 flex gap-2">
            @foreach ([
                'platform' => 'Platform Events',
                'audit' => 'Admin Actions',
            ] as $key => $label)
                <button
                    wire:click="$set('activeTab', '{{ $key }}')"
                    @class([
                        'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-(--kn-honey)/25 cursor-pointer',
                        'bg-(--kn-honey) text-(--kn-on-honey)' => $activeTab === $key,
                        'bg-transparent text-(--kn-honey-text)' => $activeTab !== $key,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Platform Events Tab --}}
    @if ($this->hasPlatformEventsTab() && $activeTab === 'platform')
        {{-- Summary Stats --}}
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-central.stat-card label="Today" value-class="text-[1.75rem] text-(--kn-ink)">
                {{ $this->eventTodayCount }}</x-central.stat-card>
            <x-central.stat-card label="This Week" value-class="text-[1.75rem] text-(--kn-ink)">
                {{ $this->eventWeekCount }}</x-central.stat-card>
            <x-central.stat-card label="Most Common" value-class="text-[1.1rem] text-(--kn-ink)">
                {{ str_replace('_', ' ', $this->mostCommonEvent) }}</x-central.stat-card>
        </div>

        {{-- Filters --}}
        <x-central.card class="mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px] flex-1">
                    <label
                        for="filter-event"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >Event</label>
                    <x-central.select id="filter-event" wire:model.live="filterEvent">
                        <option value="">All Events</option>
                        @foreach (\App\Filament\Central\Pages\Activity::getEventTypes() as $event)
                            <option value="{{ $event }}">{{ str_replace('_', ' ', ucfirst($event)) }}</option>
                        @endforeach
                    </x-central.select>
                </div>
                <div class="min-w-[140px] flex-1">
                    <label
                        for="filter-event-from"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >From</label>
                    <x-central.input id="filter-event-from" type="date" wire:model.live="filterEventDateFrom" />
                </div>
                <div class="min-w-[140px] flex-1">
                    <label
                        for="filter-event-to"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >To</label>
                    <x-central.input id="filter-event-to" type="date" wire:model.live="filterEventDateTo" />
                </div>
                <div class="min-w-[200px] flex-[2]">
                    <label
                        for="filter-event-search"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >Search</label>
                    <x-central.input
                        id="filter-event-search"
                        wire:model.live.debounce.300ms="filterEventSearch"
                        placeholder="Search descriptions or tenant…"
                    />
                </div>
                <button
                    type="button"
                    wire:click="resetEventFilters"
                    class="inline-flex cursor-pointer items-center gap-1 pb-2 text-[0.75rem] whitespace-nowrap text-(--kn-muted) transition-colors hover:text-(--kn-honey-text)"
                >
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                    Reset
                </button>
            </div>
        </x-central.card>

        {{-- Timeline --}}
        @php $activities = $this->getActivities(); @endphp
        @if ($activities->isEmpty())
            <x-central.card padding="py-16 px-8" class="text-center">
                <x-heroicon-o-clipboard-document-list class="mx-auto mb-4 block h-12 w-12 text-(--kn-muted)" />
                <div class="mb-2 text-lg font-semibold text-(--kn-ink)">No activity found</div>
                <div class="text-sm text-(--kn-muted)">Try clearing your filters or wait for new platform events.</div>
            </x-central.card>
        @else
            <div class="relative pl-8">
                <div class="absolute top-0 bottom-0 left-2 w-0.5 bg-(--kn-honey)"></div>
                @foreach ($activities as $activity)
                    @php
                        $eventIcon = \App\Filament\Central\Pages\Activity::getEventIcon($activity->event);
                        $iconClass = \App\Filament\Central\Pages\Activity::getEventIconColorClass($activity->event);
                        $borderClass = \App\Filament\Central\Pages\Activity::getEventBorderColorClass($activity->event);
                        $badgeClass = match (true) {
                            str_contains($iconClass, 'danger') => 'bg-(--kn-danger-tint) text-(--kn-danger)',
                            str_contains($iconClass, 'muted'), str_contains($iconClass, 'ink-2') => 'bg-(--kn-surface-hover) text-(--kn-ink-2)',
                            default => 'bg-(--kn-warning-tint) text-(--kn-honey-text)',
                        };
                    @endphp
                    <div class="relative mb-4">
                        <div class="absolute -left-7 top-4 w-2.5 h-2.5 rounded-full border-2 border-(--kn-surface) {{ str_replace('border-', 'bg-', $borderClass) }}"></div>

                        <x-central.card padding="py-4 px-5">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] {{ $badgeClass }}">
                                        <x-filament::icon :icon="$eventIcon" class="h-3 w-3" />
                                        {{ str_replace('_', ' ', $activity->event) }}
                                    </span>
                                    @if ($activity->tenant_id)
                                        <span class="text-xs text-(--kn-honey-text)">
                                            Tenant #{{ $activity->tenant_id }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs whitespace-nowrap text-(--kn-muted)">{{ $activity->created_at->format('M d, H:i') }}</span>
                            </div>
                            <div class="text-sm text-(--kn-ink)">{{ $activity->description }}</div>
                        </x-central.card>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Admin Actions Tab --}}
    @if ($activeTab === 'audit')
        {{-- Summary Stats --}}
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-central.stat-card label="Today" value-class="text-[1.75rem] text-(--kn-ink)">
                {{ $this->todayCount }}</x-central.stat-card>
            <x-central.stat-card label="This Week" value-class="text-[1.75rem] text-(--kn-ink)">
                {{ $this->weekCount }}</x-central.stat-card>
            <x-central.stat-card label="Most Common" value-class="text-[1.1rem] text-(--kn-ink)">
                {{ str_replace('_', ' ', $this->mostCommonAction) }}</x-central.stat-card>
        </div>

        {{-- Filters --}}
        <x-central.card class="mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px] flex-1">
                    <label
                        for="filter-action"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >Action</label>
                    <x-central.select id="filter-action" wire:model.live="filterAction">
                        <option value="">All Actions</option>
                        @foreach (\App\Filament\Central\Pages\Activity::getActionTypes() as $action)
                            <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                        @endforeach
                    </x-central.select>
                </div>
                <div class="min-w-[140px] flex-1">
                    <label
                        for="filter-audit-from"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >From</label>
                    <x-central.input id="filter-audit-from" type="date" wire:model.live="filterDateFrom" />
                </div>
                <div class="min-w-[140px] flex-1">
                    <label
                        for="filter-audit-to"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >To</label>
                    <x-central.input id="filter-audit-to" type="date" wire:model.live="filterDateTo" />
                </div>
                <div class="min-w-[200px] flex-[2]">
                    <label
                        for="filter-audit-search"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >Search</label>
                    <x-central.input
                        id="filter-audit-search"
                        wire:model.live.debounce.300ms="filterSearch"
                        placeholder="Search descriptions…"
                    />
                </div>
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex cursor-pointer items-center gap-1 pb-2 text-[0.75rem] whitespace-nowrap text-(--kn-muted) transition-colors hover:text-(--kn-honey-text)"
                >
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                    Reset
                </button>
            </div>
        </x-central.card>

        {{-- Timeline --}}
        <div class="relative pl-8">
            <div class="absolute top-0 bottom-0 left-2 w-0.5 bg-(--kn-honey)"></div>

            @forelse ($this->logs as $log)
                @php
                    $actionDotClass = \App\Filament\Central\Pages\Activity::getActionColorClass($log->action);
                    $actionPillClass = \App\Filament\Central\Pages\Activity::getActionPillClass($log->action);
                @endphp
                <div class="relative mb-4">
                    <div class="absolute -left-7 top-4 w-2.5 h-2.5 rounded-full border-2 border-(--kn-surface) {{ $actionDotClass }}"></div>

                    <x-central.card padding="py-4 px-5">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] {{ $actionPillClass }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                                @if ($log->target_type)
                                    <span class="text-xs text-(--kn-honey-text)">
                                        {{ $log->target_type }}{{ $log->target_id ? ' #' . $log->target_id : '' }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-mono text-xs text-(--kn-muted)">{{ $log->ip_address ?? '—' }}</span>
                                <span class="text-xs whitespace-nowrap text-(--kn-muted)">{{ $log->created_at->format('M d, H:i') }}</span>
                            </div>
                        </div>
                        <div class="text-sm text-(--kn-ink)">{{ $log->description }}</div>
                    </x-central.card>
                </div>
            @empty
                <x-central.card padding="py-16 px-8" class="text-center">
                    <x-heroicon-o-shield-check class="mx-auto mb-4 block h-12 w-12 text-(--kn-muted)" />
                    <div class="mb-2 text-lg font-semibold text-(--kn-ink)">No audit log entries found</div>
                    <div class="text-sm text-(--kn-muted)">Try clearing your filters or wait for admin actions.</div>
                </x-central.card>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($this->logs->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <span class="text-[0.8rem] text-(--kn-muted)">
                    Page {{ $this->logs->currentPage() }} of {{ $this->logs->lastPage() }} ({{ $this->logs->total() }} entries)
                </span>
                <div class="flex gap-2">
                    @if ($this->logs->currentPage() > 1)
                        <x-central.button variant="secondary" size="sm" wire:click="previousPage">
                            ← Previous</x-central.button>
                    @endif
                    @if ($this->logs->hasMorePages())
                        <x-central.button variant="secondary" size="sm" wire:click="nextPage">Next →</x-central.button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
