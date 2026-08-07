<x-filament-widgets::widget>
    <x-central.card>
        <div class="mb-4 flex items-center justify-between">
            <div class="text-base font-bold text-white">Recent Audit Log</div>
            <a href="{{ \App\Filament\Central\Pages\Activity::getUrl() }}" class="text-honey text-xs no-underline"
                >View all →</a>
        </div>
        @forelse ($this->recentLogs as $log)
            <div @class([
                'flex items-center gap-3 px-3 py-2 rounded-lg mb-1.5',
                'bg-honey/5' => $loop->even,
            ])>
                <span class="text-cinnamon min-w-[5rem] text-[0.7rem] whitespace-nowrap">{{ $log->created_at->diffForHumans(short: true) }}</span>
                <span class="inline-block px-2 py-0.5 rounded-full text-[0.65rem] font-semibold text-white whitespace-nowrap {{ \App\Filament\Central\Pages\Activity::getActionColorClass($log->action) }}">
                    {{ str_replace('_', ' ', $log->action) }}
                </span>
                <span class="text-parchment flex-1 truncate text-[0.8rem]">{{ $log->description }}</span>
                <span class="text-cinnamon font-mono text-[0.7rem]">{{ $log->ip_address ?? '' }}</span>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-heroicon-o-clipboard-document-list class="text-cinnamon mx-auto mb-2 block h-8 w-8" />
                <div class="text-cinnamon text-[0.8rem]">No audit entries yet.</div>
            </div>
        @endforelse
    </x-central.card>
</x-filament-widgets::widget>
