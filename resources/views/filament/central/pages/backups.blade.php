<x-filament-panels::page>
    @php
        $backups = $this->getBackups();
        $totalSize = collect($backups)->sum('size');
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-[280px] flex-1">
            <p class="text-cinnamon m-0 text-sm">
                Snapshot of the central + all tenant SQLite databases. Backups are written to the
                <code class="text-honey font-mono text-[0.8rem]">/backups</code> directory outside the Forge release
                folder, so they survive deploys.
            </p>
        </div>
        <button
            type="button"
            wire:click="runBackup"
            wire:loading.attr="disabled"
            wire:confirm="Run a fresh backup now?"
            class="bg-honey text-warm-black hover:bg-golden inline-flex cursor-pointer items-center gap-1.5 rounded-lg px-4 py-2 text-[0.8rem] font-bold transition-colors disabled:cursor-wait disabled:opacity-50"
        >
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            <span wire:loading.remove wire:target="runBackup">Run Backup Now</span>
            <span wire:loading wire:target="runBackup">Running…</span>
        </button>
    </div>

    {{-- Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-central.stat-card label="Total Backups" value-class="text-[1.75rem] text-white">
            {{ count($backups) }}</x-central.stat-card>
        <x-central.stat-card label="Total Size" value-class="text-[1.5rem] text-white">
            {{ \App\Filament\Central\Pages\Backups::formatBytes($totalSize) }}</x-central.stat-card>
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
            <x-heroicon-o-archive-box class="text-cinnamon/40 mx-auto mb-4 block h-12 w-12" />
            <div class="mb-2 text-lg font-semibold text-white">No backups yet</div>
            <div class="text-cinnamon text-sm">
                Click <strong>Run Backup Now</strong> above to create your first backup.
            </div>
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
                                <span class="font-mono text-[0.85rem] text-white">{{ $backup['name'] }}</span>
                            </x-central.td>
                            <x-central.td>
                                @if ($backup['created_at'])
                                    <div class="text-parchment text-[0.85rem]">
                                        {{ $backup['created_at']->format('M j, Y · g:i A') }}
                                    </div>
                                    <div class="text-cinnamon text-[0.7rem]">
                                        {{ $backup['created_at']->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-cinnamon">—</span>
                                @endif
                            </x-central.td>
                            <x-central.td align="right" tone="white">
                                {{ \App\Filament\Central\Pages\Backups::formatBytes($backup['size']) }}</x-central.td>
                            <x-central.td align="right" tone="white">{{ $backup['tenant_count'] }}</x-central.td>
                            <x-central.td align="center">
                                @if ($backup['central'])
                                    <x-heroicon-o-check-circle class="inline h-4 w-4 text-emerald-400" />
                                @else
                                    <x-heroicon-o-no-symbol class="text-cinnamon inline h-4 w-4" />
                                @endif
                            </x-central.td>
                            <x-central.td align="right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('central.backups.download', ['name' => $backup['name']]) }}"
                                        class="text-honey hover:text-golden inline-flex items-center gap-1 text-[0.75rem] font-semibold no-underline transition-colors"
                                    >
                                        <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5" />
                                        Download
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="deleteBackup('{{ $backup['name'] }}')"
                                        wire:confirm="Delete backup {{ $backup['name'] }}? This cannot be undone."
                                        class="inline-flex cursor-pointer items-center gap-1 text-[0.75rem] font-semibold text-red-400 transition-colors hover:text-red-300"
                                    >
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
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
