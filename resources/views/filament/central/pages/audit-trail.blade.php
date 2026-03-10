<x-filament-panels::page>
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
                @foreach(\App\Filament\Central\Pages\AuditTrail::getActionTypes() as $action)
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
        {{-- Left border line --}}
        <div style="position: absolute; left: 0.5rem; top: 0; bottom: 0; width: 2px; background: #d4920c;"></div>

        @forelse($this->logs as $log)
            <div style="position: relative; margin-bottom: 1rem;">
                {{-- Dot --}}
                <div style="position: absolute; left: -1.75rem; top: 1rem; width: 10px; height: 10px; border-radius: 50%; background: {{ \App\Filament\Central\Pages\AuditTrail::getActionColor($log->action) }}; border: 2px solid #1c1410;"></div>

                <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1rem 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #fff; background: {{ \App\Filament\Central\Pages\AuditTrail::getActionColor($log->action) }};">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                            @if($log->target_type)
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
    @if($this->logs->hasPages())
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
            <span style="color: #8b6844; font-size: 0.8rem;">
                Page {{ $this->logs->currentPage() }} of {{ $this->logs->lastPage() }} ({{ $this->logs->total() }} entries)
            </span>
            <div style="display: flex; gap: 0.5rem;">
                @if($this->logs->currentPage() > 1)
                    <button wire:click="previousPage" style="background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.4rem 0.75rem; color: #d4920c; font-size: 0.8rem; cursor: pointer; font-weight: 600;">← Previous</button>
                @endif
                @if($this->logs->hasMorePages())
                    <button wire:click="nextPage" style="background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.4rem 0.75rem; color: #d4920c; font-size: 0.8rem; cursor: pointer; font-weight: 600;">Next →</button>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
