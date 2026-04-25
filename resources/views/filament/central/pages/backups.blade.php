<x-filament-panels::page>
    @php
        $backups = $this->getBackups();
        $totalSize = collect($backups)->sum('size');
    @endphp

    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div class="flex-1 min-w-[280px]">
            <p class="text-cinnamon text-sm m-0">
                Snapshot of the central + all tenant SQLite databases. Backups are written to the <code class="text-honey font-mono text-[0.8rem]">/backups</code> directory outside the Forge release folder, so they survive deploys.
            </p>
        </div>
        <button type="button"
            wire:click="runBackup"
            wire:loading.attr="disabled"
            wire:confirm="Run a fresh backup now?"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[0.8rem] font-bold bg-honey text-warm-black hover:bg-golden transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-wait">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            <span wire:loading.remove wire:target="runBackup">Run Backup Now</span>
            <span wire:loading wire:target="runBackup">Running…</span>
        </button>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-central.stat-card label="Total Backups" value-class="text-[1.75rem] text-white">{{ count($backups) }}</x-central.stat-card>
        <x-central.stat-card label="Total Size" value-class="text-[1.5rem] text-white">{{ \App\Filament\Central\Pages\Backups::formatBytes($totalSize) }}</x-central.stat-card>
        <x-central.stat-card label="Latest" value-class="text-[1rem] text-white">
            @if (count($backups) > 0)
                {{ $backups[0]['created_at']?->diffForHumans() ?? '—' }}
            @else
                —
            @endif
        </x-central.stat-card>
    </div>

    @if (empty($backups))
        <x-central.card padding="py-16 px-8" class="text-center">
            <x-heroicon-o-archive-box class="w-12 h-12 text-cinnamon/40 mx-auto mb-4 block" />
            <div class="text-white text-lg font-semibold mb-2">No backups yet</div>
            <div class="text-cinnamon text-sm">Click <strong>Run Backup Now</strong> above to create your first backup.</div>
        </x-central.card>
    @else
        <x-central.card padding="p-0" class="overflow-hidden">
            <x-central.table>
                <thead>
                    <x-central.tr>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-left">Backup</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-left">Created</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Size</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Tenant DBs</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-center">Central DB</x-central.eyebrow>
                        <x-central.eyebrow as="th" class="px-4 py-3 text-right">Actions</x-central.eyebrow>
                    </x-central.tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        <x-central.tr>
                            <x-central.td>
                                <span class="text-white font-mono text-[0.85rem]">{{ $backup['name'] }}</span>
                            </x-central.td>
                            <x-central.td>
                                @if ($backup['created_at'])
                                    <div class="text-parchment text-[0.85rem]">{{ $backup['created_at']->format('M j, Y · g:i A') }}</div>
                                    <div class="text-cinnamon text-[0.7rem]">{{ $backup['created_at']->diffForHumans() }}</div>
                                @else
                                    <span class="text-cinnamon">—</span>
                                @endif
                            </x-central.td>
                            <x-central.td align="right" tone="white">{{ \App\Filament\Central\Pages\Backups::formatBytes($backup['size']) }}</x-central.td>
                            <x-central.td align="right" tone="white">{{ $backup['tenant_count'] }}</x-central.td>
                            <x-central.td align="center">
                                @if ($backup['central'])
                                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-400 inline" />
                                @else
                                    <x-heroicon-o-no-symbol class="w-4 h-4 text-cinnamon inline" />
                                @endif
                            </x-central.td>
                            <x-central.td align="right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('central.backups.download', ['name' => $backup['name']]) }}"
                                        class="inline-flex items-center gap-1 text-[0.75rem] font-semibold text-honey hover:text-golden transition-colors no-underline">
                                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                        Download
                                    </a>
                                    <button type="button"
                                        wire:click="deleteBackup('{{ $backup['name'] }}')"
                                        wire:confirm="Delete backup {{ $backup['name'] }}? This cannot be undone."
                                        class="inline-flex items-center gap-1 text-[0.75rem] font-semibold text-red-400 hover:text-red-300 transition-colors cursor-pointer">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        Delete
                                    </button>
                                </div>
                            </x-central.td>
                        </x-central.tr>
                    @endforeach
                </tbody>
            </x-central.table>
        </x-central.card>
    @endif
</x-filament-panels::page>
