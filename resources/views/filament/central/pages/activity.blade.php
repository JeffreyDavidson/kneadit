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
        @php $activities = $this->getActivities(); @endphp

        @if ($activities->isEmpty())
            <div class="text-center py-16 px-8">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-cinnamon mx-auto mb-4 block" />
                <div class="text-white text-lg font-semibold mb-2">No activity yet</div>
                <div class="text-cinnamon text-sm">Platform events will appear here as they happen — tenant signups, plan changes, and more.</div>
            </div>
        @else
            <div class="relative pl-10">
                <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-honey/20"></div>
                @foreach ($activities as $activity)
                    @php
                        $eventIcon = \App\Filament\Central\Pages\Activity::getEventIcon($activity->event);
                        $iconClass = \App\Filament\Central\Pages\Activity::getEventIconColorClass($activity->event);
                        $borderClass = \App\Filament\Central\Pages\Activity::getEventBorderColorClass($activity->event);
                    @endphp
                    <div class="relative mb-6">
                        <div class="absolute left-[-2.5rem] top-0.5 w-7 h-7 rounded-full flex items-center justify-center bg-warm-black z-10 border-2 {{ $borderClass }}">
                            <x-dynamic-component :component="$eventIcon ?: 'heroicon-o-clock'" class="w-3.5 h-3.5 {{ $iconClass }}" />
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
            <div class="flex-1 min-w-[180px]">
                <x-central.eyebrow as="label" class="block mb-1">Action</x-central.eyebrow>
                <x-central.select wire:model.live="filterAction">
                    <option value="">All Actions</option>
                    @foreach (\App\Filament\Central\Pages\Activity::getActionTypes() as $action)
                        <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                    @endforeach
                </x-central.select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <x-central.eyebrow as="label" class="block mb-1">From</x-central.eyebrow>
                <x-central.input type="date" wire:model.live="filterDateFrom" />
            </div>
            <div class="flex-1 min-w-[140px]">
                <x-central.eyebrow as="label" class="block mb-1">To</x-central.eyebrow>
                <x-central.input type="date" wire:model.live="filterDateTo" />
            </div>
            <div class="flex-[2] min-w-[200px]">
                <x-central.eyebrow as="label" class="block mb-1">Search</x-central.eyebrow>
                <x-central.input wire:model.live.debounce.300ms="filterSearch" placeholder="Search descriptions..." />
            </div>
            <x-central.button variant="secondary" wire:click="resetFilters" class="whitespace-nowrap">Reset</x-central.button>
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
                <div class="text-center p-8 text-cinnamon italic">
                    No audit log entries found.
                </div>
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
