<x-filament-widgets::widget>
    <x-central.card>
        <div class="mb-4 flex items-center justify-between">
            <div class="text-base font-bold text-(--kn-ink)">Recent Audit Log</div>
            <a
                href="{{ \App\Filament\Central\Pages\Activity::getUrl() }}"
                class="text-xs text-(--kn-honey-text) no-underline"
            >View all →</a>
        </div>
        @forelse ($this->recentLogs as $log)
            <div @class([
                'flex items-center gap-3 px-3 py-2 rounded-lg mb-1.5',
                'bg-(--kn-surface-hover)' => $loop->even,
            ])>
                <span class="min-w-[5rem] text-[0.7rem] whitespace-nowrap text-(--kn-muted)">{{ $log->created_at->diffForHumans(short: true) }}</span>
                <span class="inline-block px-2 py-0.5 rounded-full text-[0.65rem] font-semibold whitespace-nowrap {{ \App\Filament\Central\Pages\Activity::getActionPillClass($log->action) }}">
                    {{ str_replace('_', ' ', $log->action) }}
                </span>
                <span class="flex-1 truncate text-[0.8rem] text-(--kn-ink)">{{ $log->description }}</span>
                <span class="font-mono text-[0.7rem] text-(--kn-muted)">{{ $log->ip_address ?? '' }}</span>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-heroicon-o-clipboard-document-list class="mx-auto mb-2 block h-8 w-8 text-(--kn-muted)" />
                <div class="text-[0.8rem] text-(--kn-muted)">No audit entries yet.</div>
            </div>
        @endforelse
    </x-central.card>
</x-filament-widgets::widget>
