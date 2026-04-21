<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        @foreach ([
            'platform' => 'Platform Events',
            'audit' => 'Admin Actions',
        ] as $key => $label)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                style="padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(212,146,12,0.25); cursor: pointer;
                    {{ $activeTab === $key ? 'background: #d4920c; color: #1c1410;' : 'background: transparent; color: #d4920c;' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Platform Events Tab --}}
    @if ($activeTab === 'platform')
        @php $activities = $this->getActivities(); @endphp

        @if ($activities->isEmpty())
            <div class="text-center py-16 px-8">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-cinnamon mx-auto mb-4 block" />
                <div class="text-white text-lg font-semibold mb-2">No activity yet</div>
                <div class="text-cinnamon text-sm">Platform events will appear here as they happen — tenant signups, plan changes, and more.</div>
            </div>
        @else
            <div style="position: relative; padding-left: 2.5rem;">
                <div style="position: absolute; left: 0.9rem; top: 0; bottom: 0; width: 2px; background: rgba(212,146,12,0.2);"></div>
                @foreach ($activities as $activity)
                    @php
                        $eventColor = \App\Filament\Central\Pages\Activity::getEventColor($activity->event);
                        $eventIcon = \App\Filament\Central\Pages\Activity::getEventIcon($activity->event);
                    @endphp
                    <div style="position: relative; margin-bottom: 1.5rem;">
                        <div class="absolute left-[-2.5rem] top-0.5 w-7 h-7 rounded-full flex items-center justify-center bg-warm-black z-10" style="border: 2px solid {{ $eventColor }};">
                            <x-dynamic-component :component="$eventIcon ?: 'heroicon-o-clock'" class="w-3.5 h-3.5" style="color: {{ $eventColor }};" />
                        </div>
                        <x-central.card padding="py-4 px-5">
                            <x-central.badge color="butter" size="sm" class="mb-1.5 bg-honey/15">{{ str_replace('_', ' ', $activity->event) }}</x-central.badge>
                            <div class="text-parchment text-sm">{{ $activity->description }}</div>
                            <div class="text-cinnamon text-xs mt-1">
                                @if ($activity->tenant_id)
                                    Tenant: {{ $activity->tenant_id }} ·
                                @endif
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </x-central.card>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Admin Actions Tab --}}
    @if ($activeTab === 'audit')
        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 gap-6 mb-6">
            <x-central.stat-card label="Today" value-class="text-[1.75rem] text-white">{{ $this->todayCount }}</x-central.stat-card>
            <x-central.stat-card label="This Week" value-class="text-[1.75rem] text-white">{{ $this->weekCount }}</x-central.stat-card>
            <x-central.stat-card label="Most Common" value-class="text-[1.1rem] text-white">{{ str_replace('_', ' ', $this->mostCommonAction) }}</x-central.stat-card>
        </div>

        {{-- Filters --}}
        <x-central.card class="mb-6 flex gap-3 flex-wrap items-end">
            <div style="flex: 1; min-width: 180px;">
                <x-central.eyebrow as="label" class="block mb-1">Action</x-central.eyebrow>
                <x-central.select wire:model.live="filterAction">
                    <option value="">All Actions</option>
                    @foreach (\App\Filament\Central\Pages\Activity::getActionTypes() as $action)
                        <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                    @endforeach
                </x-central.select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <x-central.eyebrow as="label" class="block mb-1">From</x-central.eyebrow>
                <x-central.input type="date" wire:model.live="filterDateFrom" />
            </div>
            <div style="flex: 1; min-width: 140px;">
                <x-central.eyebrow as="label" class="block mb-1">To</x-central.eyebrow>
                <x-central.input type="date" wire:model.live="filterDateTo" />
            </div>
            <div style="flex: 2; min-width: 200px;">
                <x-central.eyebrow as="label" class="block mb-1">Search</x-central.eyebrow>
                <x-central.input wire:model.live.debounce.300ms="filterSearch" placeholder="Search descriptions..." />
            </div>
            <x-central.button variant="secondary" wire:click="resetFilters" class="whitespace-nowrap">Reset</x-central.button>
        </x-central.card>

        {{-- Timeline --}}
        <div style="position: relative; padding-left: 2rem;">
            <div style="position: absolute; left: 0.5rem; top: 0; bottom: 0; width: 2px; background: #d4920c;"></div>

            @forelse ($this->logs as $log)
                <div style="position: relative; margin-bottom: 1rem;">
                    <div style="position: absolute; left: -1.75rem; top: 1rem; width: 10px; height: 10px; border-radius: 50%; background: {{ \App\Filament\Central\Pages\Activity::getActionColor($log->action) }}; border: 2px solid #1c1410;"></div>

                    <x-central.card padding="py-4 px-5">
                        <div class="flex justify-between items-center flex-wrap gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span style="background: {{ \App\Filament\Central\Pages\Activity::getActionColor($log->action) }};" class="inline-block px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em] text-white">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                                @if ($log->target_type)
                                    <span style="color: #d4920c; font-size: 0.75rem;">
                                        {{ $log->target_type }}{{ $log->target_id ? ' #' . $log->target_id : '' }}
                                    </span>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: #8b6844; font-size: 0.75rem; font-family: monospace;">{{ $log->ip_address ?? '—' }}</span>
                                <span style="color: #8b6844; font-size: 0.75rem; white-space: nowrap;">{{ $log->created_at->format('M d, H:i') }}</span>
                            </div>
                        </div>
                        <div class="text-parchment text-sm">{{ $log->description }}</div>
                    </x-central.card>
                </div>
            @empty
                <div style="text-align: center; padding: 2rem; color: #8b6844; font-style: italic;">
                    No audit log entries found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($this->logs->hasPages())
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <span style="color: #8b6844; font-size: 0.8rem;">
                    Page {{ $this->logs->currentPage() }} of {{ $this->logs->lastPage() }} ({{ $this->logs->total() }} entries)
                </span>
                <div style="display: flex; gap: 0.5rem;">
                    @if ($this->logs->currentPage() > 1)
                        <x-central.button variant="secondary" wire:click="previousPage">← Previous</x-central.button>
                    @endif
                    @if ($this->logs->hasMorePages())
                        <x-central.button variant="secondary" wire:click="nextPage">Next →</x-central.button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
