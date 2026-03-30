<x-filament-widgets::widget>
    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div style="color: #ffffff; font-weight: 700; font-size: 1rem;">Recent Audit Log</div>
            <a href="{{ \App\Filament\Central\Pages\Activity::getUrl() }}" style="color: #d4920c; font-size: 0.75rem; text-decoration: none;">View all →</a>
        </div>
        @forelse ($this->recentLogs as $log)
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: 8px; margin-bottom: 0.375rem;{{ $loop->even ? ' background: rgba(212,146,12,0.04);' : '' }}">
                <span style="color: #8b6844; font-size: 0.7rem; white-space: nowrap; min-width: 5rem;">{{ $log->created_at->diffForHumans(short: true) }}</span>
                <span style="display: inline-block; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; color: #ffffff; background: {{ \App\Filament\Central\Pages\Activity::getActionColor($log->action) }}; white-space: nowrap;">
                    {{ str_replace('_', ' ', $log->action) }}
                </span>
                <span style="color: #faf0d6; font-size: 0.8rem; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log->description }}</span>
                <span style="color: #8b6844; font-size: 0.7rem; font-family: monospace;">{{ $log->ip_address ?? '' }}</span>
            </div>
        @empty
            <div style="padding: 2rem; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 32px; height: 32px; color: #8b6844; margin: 0 auto 0.5rem; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
                <div style="color: #8b6844; font-size: 0.8rem;">No audit entries yet.</div>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>
