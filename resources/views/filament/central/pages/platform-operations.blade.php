<x-filament-panels::page>
    <div class="mb-6">
        <p class="m-0 text-sm text-(--kn-muted)">
            Manually trigger maintenance jobs that normally run on the cron schedule. Use sparingly — running too often
            can spam tenants with duplicate emails.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getCommands() as $cmd)
            @php
                $tone = match ($cmd['color']) {
                    'emerald' => ['bg' => 'bg-(--kn-success-tint) border-(--kn-success)/20', 'iconBg' => 'bg-(--kn-success-tint) border-(--kn-success)/25', 'iconColor' => 'text-(--kn-success)'],
                    'amber' => ['bg' => 'bg-(--kn-warning-tint) border-(--kn-warning)/20', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-warning)/25', 'iconColor' => 'text-(--kn-warning)'],
                    'red' => ['bg' => 'bg-(--kn-danger-tint) border-(--kn-danger)/20', 'iconBg' => 'bg-(--kn-danger-tint) border-(--kn-danger)/25', 'iconColor' => 'text-(--kn-danger)'],
                    'sky' => ['bg' => 'bg-(--kn-info-tint) border-(--kn-info)/20', 'iconBg' => 'bg-(--kn-info-tint) border-(--kn-info)/25', 'iconColor' => 'text-(--kn-info)'],
                    'gold' => ['bg' => 'bg-(--kn-surface-hover) border-(--kn-border)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-honey)/25', 'iconColor' => 'text-(--kn-honey-text)'],
                    default => ['bg' => 'bg-(--kn-surface-hover) border-(--kn-border)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-honey)/25', 'iconColor' => 'text-(--kn-honey-text)'],
                };
                $iconComponent = $cmd['icon'] instanceof \Filament\Support\Icons\Heroicon ? 'heroicon-'.$cmd['icon']->value : $cmd['icon'];
                $lastRun = $this->getLastRun($cmd['key']);
                $lastRunCarbon = $lastRun ? \Illuminate\Support\Carbon::parse($lastRun) : null;
                $taskStatus = $this->getTaskStatus($cmd['key']);
            @endphp

            <x-central.card class="flex flex-col {{ $tone['bg'] }}">
                <div class="mb-3 flex items-start gap-3">
                    <div class="shrink-0 w-11 h-11 rounded-xl border {{ $tone['iconBg'] }} flex items-center justify-center">
                        <x-dynamic-component :component="$iconComponent" class="w-5 h-5 {{ $tone['iconColor'] }}" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[0.95rem] font-bold text-(--kn-ink)">{{ $cmd['label'] }}</div>
                        <div class="mt-0.5 font-mono text-[0.7rem] text-(--kn-muted)">{{ $cmd['key'] }}</div>
                    </div>
                </div>

                <div class="mb-4 flex-1 text-[0.8rem] leading-snug text-(--kn-muted)">{{ $cmd['description'] }}</div>

                <div class="flex items-center justify-between gap-2">
                    <div class="text-[0.7rem] text-(--kn-muted)">
                        @if ($lastRunCarbon)
                            <span class="text-(--kn-muted)">Last run</span>
                            <span
                                class="text-(--kn-ink)"
                                title="{{ $lastRunCarbon->format('M j, Y · g:i A') }}"
                            >{{ $lastRunCarbon->diffForHumans() }}</span>
                        @else
                            <span class="text-(--kn-muted)">Never run from this UI</span>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="run('{{ $cmd['key'] }}')"
                        wire:loading.attr="disabled"
                        wire:confirm="Run {{ $cmd['label'] }} now?"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-3 py-1.5 text-[0.75rem] font-semibold text-(--kn-honey-text) transition-colors hover:bg-(--kn-honey) hover:text-(--kn-on-honey) disabled:cursor-wait disabled:opacity-50"
                    >
                        <x-heroicon-o-play class="h-3.5 w-3.5" />
                        <span wire:loading.remove wire:target="run('{{ $cmd['key'] }}')">Run Now</span>
                        <span wire:loading wire:target="run('{{ $cmd['key'] }}')">Running…</span>
                    </button>
                </div>
                @if ($taskStatus !== [])
                    <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 border-t border-(--kn-border) pt-3 text-[0.68rem] text-(--kn-muted)">
                        <span
                            @class([
                                'font-semibold',
                                'text-(--kn-success)' => ($taskStatus['status'] ?? null) === 'succeeded',
                                'text-(--kn-danger)' => ($taskStatus['status'] ?? null) === 'failed',
                                'text-(--kn-warning)' => ($taskStatus['status'] ?? null) === 'running',
                            ])
                        >{{ ucfirst($taskStatus['status'] ?? 'unknown') }}</span>
                        @if (isset($taskStatus['runtime_seconds']))
                            <span>{{ number_format((float) $taskStatus['runtime_seconds'], 2) }}s</span>
                        @endif
                        @if (($taskStatus['error'] ?? null) !== null)
                            <span
                                class="w-full truncate text-(--kn-danger)"
                                title="{{ $taskStatus['error'] }}"
                            >{{ $taskStatus['error'] }}</span>
                        @endif
                    </div>
                @endif
            </x-central.card>
        @endforeach
    </div>
</x-filament-panels::page>
