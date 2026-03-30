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
                        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1rem 1.25rem;">
                            <div style="display: inline-block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgba(212,146,12,0.15); color: #f5d88e; margin-bottom: 0.375rem;">{{ str_replace('_', ' ', $activity->event) }}</div>
                            <div style="color: #faf0d6; font-size: 0.875rem;">{{ $activity->description }}</div>
                            <div style="color: #8b6844; font-size: 0.75rem; margin-top: 0.25rem;">
                                @if ($activity->tenant_id)
                                    Tenant: {{ $activity->tenant_id }} ·
                                @endif
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Admin Actions Tab --}}
    @if ($activeTab === 'audit')
        {{-- Summary Stats --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">Today</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem;">{{ $this->todayCount }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">This Week</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem;">{{ $this->weekCount }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">Most Common</div>
                <div style="color: white; font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem;">{{ str_replace('_', ' ', $this->mostCommonAction) }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
            <div style="flex: 1; min-width: 180px;">
                <label style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.25rem;">Action</label>
                <select wire:model.live="filterAction" style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.5rem 2rem 0.5rem 0.75rem; color: #faf0d6; font-size: 0.875rem; outline: none; -webkit-appearance: none; appearance: none; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23d4920c%22 stroke-width=%222%22><path d=%22M6 9l6 6 6-6%22/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center;">
                    <option value="">All Actions</option>
                    @foreach (\App\Filament\Central\Pages\Activity::getActionTypes() as $action)
                        <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.25rem;">From</label>
                <input type="date" wire:model.live="filterDateFrom" style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.5rem; color: #faf0d6; font-size: 0.875rem; outline: none; box-sizing: border-box;">
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.25rem;">To</label>
                <input type="date" wire:model.live="filterDateTo" style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.5rem; color: #faf0d6; font-size: 0.875rem; outline: none; box-sizing: border-box;">
            </div>
            <div style="flex: 2; min-width: 200px;">
                <label style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.25rem;">Search</label>
                <input type="text" wire:model.live.debounce.300ms="filterSearch" placeholder="Search descriptions..." style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.5rem; color: #faf0d6; font-size: 0.875rem; outline: none; box-sizing: border-box;">
            </div>
            <button wire:click="resetFilters" style="background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.5rem 1rem; color: #d4920c; font-size: 0.875rem; cursor: pointer; white-space: nowrap; font-weight: 600;">
                Reset
            </button>
        </div>

        {{-- Timeline --}}
        <div style="position: relative; padding-left: 2rem;">
            <div style="position: absolute; left: 0.5rem; top: 0; bottom: 0; width: 2px; background: #d4920c;"></div>

            @forelse ($this->logs as $log)
                <div style="position: relative; margin-bottom: 1rem;">
                    <div style="position: absolute; left: -1.75rem; top: 1rem; width: 10px; height: 10px; border-radius: 50%; background: {{ \App\Filament\Central\Pages\Activity::getActionColor($log->action) }}; border: 2px solid #1c1410;"></div>

                    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1rem 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #fff; background: {{ \App\Filament\Central\Pages\Activity::getActionColor($log->action) }};">
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
                        <div style="color: #faf0d6; font-size: 0.875rem;">{{ $log->description }}</div>
                    </div>
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
                        <button wire:click="previousPage" style="background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.4rem 0.75rem; color: #d4920c; font-size: 0.8rem; cursor: pointer; font-weight: 600;">← Previous</button>
                    @endif
                    @if ($this->logs->hasMorePages())
                        <button wire:click="nextPage" style="background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.4rem 0.75rem; color: #d4920c; font-size: 0.8rem; cursor: pointer; font-weight: 600;">Next →</button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
