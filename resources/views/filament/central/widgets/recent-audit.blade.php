<x-filament-widgets::widget>
    <x-central.card>
        <div class="flex justify-between items-center mb-4">
            <div class="text-white font-bold text-base">Recent Audit Log</div>
            <a href="{{ \App\Filament\Central\Pages\Activity::getUrl() }}" class="text-honey text-xs no-underline">View all →</a>
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
            <div class="p-8 text-center">
                <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-cinnamon mx-auto mb-2 block" />
                <div class="text-cinnamon text-[0.8rem]">No audit entries yet.</div>
            </div>
        @endforelse
    </x-central.card>
</x-filament-widgets::widget>
