<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div class="flex gap-2 mb-6">
        @foreach ([
            'platform' => 'Platform Events',
            'audit' => 'Admin Actions',
        ] as $key => $label)
            <button wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-honey/25 cursor-pointer',
                    'bg-honey text-warm-black' => $activeTab === $key,
                    'bg-transparent text-honey' => $activeTab !== $key,
                ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Platform Events Tab --}}
    @if ($activeTab === 'platform')
        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-central.stat-card label="Today" value-class="text-[1.75rem] text-white">{{ $this->eventTodayCount }}</x-central.stat-card>
            <x-central.stat-card label="This Week" value-class="text-[1.75rem] text-white">{{ $this->eventWeekCount }}</x-central.stat-card>
            <x-central.stat-card label="Most Common" value-class="text-[1.1rem] text-white">{{ str_replace('_', ' ', $this->mostCommonEvent) }}</x-central.stat-card>
        </div>

        {{-- Filters --}}
        <x-central.card class="mb-6">
            <div class="flex gap-3 flex-wrap items-end">
                <div class="flex-1 min-w-[180px]">
                    <label for="filter-event" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Event</label>
                    <x-central.select id="filter-event" wire:model.live="filterEvent">
                        <option value="">All Events</option>
                        @foreach (\App\Filament\Central\Pages\Activity::getEventTypes() as $event)
                            <option value="{{ $event }}">{{ str_replace('_', ' ', ucfirst($event)) }}</option>
                        @endforeach
                    </x-central.select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="filter-event-from" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">From</label>
                    <x-central.input id="filter-event-from" type="date" wire:model.live="filterEventDateFrom" />
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="filter-event-to" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">To</label>
                    <x-central.input id="filter-event-to" type="date" wire:model.live="filterEventDateTo" />
                </div>
                <div class="flex-[2] min-w-[200px]">
                    <label for="filter-event-search" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Search</label>
                    <x-central.input id="filter-event-search" wire:model.live.debounce.300ms="filterEventSearch" placeholder="Search descriptions or tenant…" />
                </div>
                <button type="button" wire:click="resetEventFilters"
                    class="inline-flex items-center gap-1 text-[0.75rem] text-cinnamon hover:text-honey transition-colors cursor-pointer whitespace-nowrap pb-2">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Reset
                </button>
            </div>
        </x-central.card>

        {{-- Timeline --}}
        @php $activities = $this->getActivities(); @endphp
        @if ($activities->isEmpty())
            <x-central.card padding="py-16 px-8" class="text-center">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-cinnamon mx-auto mb-4 block" />
                <div class="text-white text-lg font-semibold mb-2">No activity found</div>
                <div class="text-cinnamon text-sm">Try clearing your filters or wait for new platform events.</div>
            </x-central.card>
        @else
            <div class="relative pl-8">
                <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-honey"></div>
                @foreach ($activities as $activity)
                    @php
                        $eventIcon = \App\Filament\Central\Pages\Activity::getEventIcon($activity->event);
                        $iconClass = \App\Filament\Central\Pages\Activity::getEventIconColorClass($activity->event);
                        $borderClass = \App\Filament\Central\Pages\Activity::getEventBorderColorClass($activity->event);
                        $badgeClass = match (true) {
                            str_contains($iconClass, 'red') => 'bg-red-500 text-white',
                            str_contains($iconClass, 'amber') => 'bg-amber-500 text-warm-black',
                            str_contains($iconClass, 'emerald') || str_contains($iconClass, 'green') => 'bg-emerald-500 text-white',
                            str_contains($iconClass, 'sky') || str_contains($iconClass, 'blue') => 'bg-sky-500 text-white',
                            default => 'bg-honey text-warm-black',
                        };
                    @endphp
                    <div class="relative mb-4">
                        <div class="absolute -left-7 top-4 w-2.5 h-2.5 rounded-full border-2 border-warm-black {{ str_replace('border-', 'bg-', $borderClass) }}"></div>

                        <x-central.card padding="py-4 px-5">
                            <div class="flex justify-between items-center flex-wrap gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] {{ $badgeClass }}">
                                        <x-filament::icon :icon="$eventIcon" class="w-3 h-3" />
                                        {{ str_replace('_', ' ', $activity->event) }}
                                    </span>
                                    @if ($activity->tenant_id)
                                        <span class="text-honey text-xs">
                                            Tenant #{{ $activity->tenant_id }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-cinnamon text-xs whitespace-nowrap">{{ $activity->created_at->format('M d, H:i') }}</span>
                            </div>
                            <div class="text-parchment text-sm">{{ $activity->description }}</div>
                        </x-central.card>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Admin Actions Tab --}}
    @if ($activeTab === 'audit')
        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-central.stat-card label="Today" value-class="text-[1.75rem] text-white">{{ $this->todayCount }}</x-central.stat-card>
            <x-central.stat-card label="This Week" value-class="text-[1.75rem] text-white">{{ $this->weekCount }}</x-central.stat-card>
            <x-central.stat-card label="Most Common" value-class="text-[1.1rem] text-white">{{ str_replace('_', ' ', $this->mostCommonAction) }}</x-central.stat-card>
        </div>

        {{-- Filters --}}
        <x-central.card class="mb-6">
            <div class="flex gap-3 flex-wrap items-end">
                <div class="flex-1 min-w-[180px]">
                    <label for="filter-action" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Action</label>
                    <x-central.select id="filter-action" wire:model.live="filterAction">
                        <option value="">All Actions</option>
                        @foreach (\App\Filament\Central\Pages\Activity::getActionTypes() as $action)
                            <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                        @endforeach
                    </x-central.select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="filter-audit-from" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">From</label>
                    <x-central.input id="filter-audit-from" type="date" wire:model.live="filterDateFrom" />
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="filter-audit-to" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">To</label>
                    <x-central.input id="filter-audit-to" type="date" wire:model.live="filterDateTo" />
                </div>
                <div class="flex-[2] min-w-[200px]">
                    <label for="filter-audit-search" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Search</label>
                    <x-central.input id="filter-audit-search" wire:model.live.debounce.300ms="filterSearch" placeholder="Search descriptions…" />
                </div>
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 text-[0.75rem] text-cinnamon hover:text-honey transition-colors cursor-pointer whitespace-nowrap pb-2">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Reset
                </button>
            </div>
        </x-central.card>

        {{-- Timeline --}}
        <div class="relative pl-8">
            <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-honey"></div>

            @forelse ($this->logs as $log)
                @php $actionBgClass = \App\Filament\Central\Pages\Activity::getActionColorClass($log->action); @endphp
                <div class="relative mb-4">
                    <div class="absolute -left-7 top-4 w-2.5 h-2.5 rounded-full border-2 border-warm-black {{ $actionBgClass }}"></div>

                    <x-central.card padding="py-4 px-5">
                        <div class="flex justify-between items-center flex-wrap gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] text-white {{ $actionBgClass }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                                @if ($log->target_type)
                                    <span class="text-honey text-xs">
                                        {{ $log->target_type }}{{ $log->target_id ? ' #' . $log->target_id : '' }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-cinnamon text-xs font-mono">{{ $log->ip_address ?? '—' }}</span>
                                <span class="text-cinnamon text-xs whitespace-nowrap">{{ $log->created_at->format('M d, H:i') }}</span>
                            </div>
                        </div>
                        <div class="text-parchment text-sm">{{ $log->description }}</div>
                    </x-central.card>
                </div>
            @empty
                <x-central.card padding="py-16 px-8" class="text-center">
                    <x-heroicon-o-shield-check class="w-12 h-12 text-cinnamon mx-auto mb-4 block" />
                    <div class="text-white text-lg font-semibold mb-2">No audit log entries found</div>
                    <div class="text-cinnamon text-sm">Try clearing your filters or wait for admin actions.</div>
                </x-central.card>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($this->logs->hasPages())
            <div class="flex justify-between items-center mt-6">
                <span class="text-cinnamon text-[0.8rem]">
                    Page {{ $this->logs->currentPage() }} of {{ $this->logs->lastPage() }} ({{ $this->logs->total() }} entries)
                </span>
                <div class="flex gap-2">
                    @if ($this->logs->currentPage() > 1)
                        <x-central.button variant="secondary" size="sm" wire:click="previousPage">← Previous</x-central.button>
                    @endif
                    @if ($this->logs->hasMorePages())
                        <x-central.button variant="secondary" size="sm" wire:click="nextPage">Next →</x-central.button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
