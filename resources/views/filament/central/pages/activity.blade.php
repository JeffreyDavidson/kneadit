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
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: #8b6844; margin: 0 auto; display: block;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                </div>
                <div style="color: #ffffff; font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">No activity yet</div>
                <div style="color: #8b6844; font-size: 0.875rem;">Platform events will appear here as they happen — tenant signups, plan changes, and more.</div>
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
                        <div style="position: absolute; left: -2.5rem; top: 0.125rem; width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #1c1410; border: 2px solid {{ $eventColor }}; z-index: 1;">
                            @if ($eventIcon === 'heroicon-o-user-plus')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            @elseif ($eventIcon === 'heroicon-o-arrow-path')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182M2.985 19.644l3.181-3.182" />
                                </svg>
                            @elseif ($eventIcon === 'heroicon-o-trash')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @endif
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
