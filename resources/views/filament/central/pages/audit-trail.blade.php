<x-filament-panels::page>
    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Today</div>
            <div style="color: #f5d88e; font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem;">{{ $this->todayCount }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">This Week</div>
            <div style="color: #f5d88e; font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem;">{{ $this->weekCount }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Most Common</div>
            <div style="color: #f5d88e; font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem;">{{ str_replace('_', ' ', $this->mostCommonAction) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
        <div style="flex: 1; min-width: 180px;">
            <label style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">Action</label>
            <select wire:model.live="filterAction" style="width: 100%; background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 0.5rem; padding: 0.5rem; color: #f5d88e; font-size: 0.875rem;">
                <option value="">All Actions</option>
                @foreach(\App\Filament\Central\Pages\AuditTrail::getActions() as $action)
                    <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 140px;">
            <label style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">From</label>
            <input type="date" wire:model.live="filterDateFrom" style="width: 100%; background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 0.5rem; padding: 0.5rem; color: #f5d88e; font-size: 0.875rem;">
        </div>
        <div style="flex: 1; min-width: 140px;">
            <label style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">To</label>
            <input type="date" wire:model.live="filterDateTo" style="width: 100%; background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 0.5rem; padding: 0.5rem; color: #f5d88e; font-size: 0.875rem;">
        </div>
        <div style="flex: 2; min-width: 200px;">
            <label style="color: #e8b04a; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.25rem;">Search</label>
            <input type="text" wire:model.live.debounce.300ms="filterSearch" placeholder="Search descriptions..." style="width: 100%; background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 0.5rem; padding: 0.5rem; color: #f5d88e; font-size: 0.875rem;">
        </div>
        <button wire:click="resetFilters" style="background: #3d2c1e; border: 1px solid #d4920c; border-radius: 0.5rem; padding: 0.5rem 1rem; color: #e8b04a; font-size: 0.875rem; cursor: pointer; white-space: nowrap;">
            Reset
        </button>
    </div>

    {{-- Log Table --}}
    <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid #3d2c1e;">
                    <th style="padding: 0.75rem 1rem; text-align: left; color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Time</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Action</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Description</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Target</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; color: #e8b04a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->logs as $log)
                    <tr style="border-bottom: 1px solid #2a1f18;">
                        <td style="padding: 0.75rem 1rem; color: #f5d88e; font-size: 0.8rem; white-space: nowrap;">
                            {{ $log->created_at->format('M d, H:i') }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; color: #fff; background: {{ \App\Filament\Central\Pages\AuditTrail::getActionColor($log->action) }};">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #f5d88e; font-size: 0.875rem;">
                            {{ $log->description }}
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #d4920c; font-size: 0.8rem;">
                            @if($log->target_type)
                                {{ $log->target_type }}{{ $log->target_id ? ' #' . $log->target_id : '' }}
                            @else
                                <span style="color: #3d2c1e;">—</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #6b7280; font-size: 0.8rem; font-family: monospace;">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: #3d2c1e; font-size: 0.875rem;">
                            No audit log entries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($this->logs->hasPages())
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <span style="color: #e8b04a; font-size: 0.8rem;">
                Page {{ $this->logs->currentPage() }} of {{ $this->logs->lastPage() }} ({{ $this->logs->total() }} entries)
            </span>
            <div style="display: flex; gap: 0.5rem;">
                @if($this->logs->currentPage() > 1)
                    <button wire:click="previousPage" style="background: #3d2c1e; border: 1px solid #d4920c; border-radius: 0.5rem; padding: 0.4rem 0.75rem; color: #e8b04a; font-size: 0.8rem; cursor: pointer;">← Previous</button>
                @endif
                @if($this->logs->hasMorePages())
                    <button wire:click="nextPage" style="background: #3d2c1e; border: 1px solid #d4920c; border-radius: 0.5rem; padding: 0.4rem 0.75rem; color: #e8b04a; font-size: 0.8rem; cursor: pointer;">Next →</button>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
