<x-filament-widgets::widget>
    <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 0.75rem; padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #e8b04a; font-size: 0.95rem; font-weight: 700; margin: 0;">Recent Audit Log</h3>
            <a href="{{ \App\Filament\Central\Pages\AuditTrail::getUrl() }}" style="color: #d4920c; font-size: 0.75rem; text-decoration: none;">View all →</a>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @forelse($this->recentLogs as $log)
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; background: #2a1f18; border-radius: 0.5rem;">
                    <span style="color: #6b7280; font-size: 0.7rem; white-space: nowrap; min-width: 5rem;">{{ $log->created_at->diffForHumans(short: true) }}</span>
                    <span style="display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; color: #fff; background: {{ \App\Filament\Central\Pages\AuditTrail::getActionColor($log->action) }}; white-space: nowrap;">
                        {{ str_replace('_', ' ', $log->action) }}
                    </span>
                    <span style="color: #f5d88e; font-size: 0.8rem; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log->description }}</span>
                    <span style="color: #3d2c1e; font-size: 0.7rem; font-family: monospace;">{{ $log->ip_address ?? '' }}</span>
                </div>
            @empty
                <div style="padding: 1rem; text-align: center; color: #3d2c1e; font-size: 0.8rem;">No audit entries yet.</div>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>
