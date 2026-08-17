<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-cinnamon m-0 text-sm">
            Manually trigger maintenance jobs that normally run on the cron schedule. Use sparingly — running too often
            can spam tenants with duplicate emails.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getCommands() as $cmd)
            @php
                $tone = match ($cmd['color']) {
                    'emerald' => ['bg' => 'bg-emerald-500/5 border-emerald-500/20', 'iconBg' => 'bg-emerald-500/15 border-emerald-500/25', 'iconColor' => 'text-emerald-400'],
                    'amber' => ['bg' => 'bg-amber-500/5 border-amber-500/20', 'iconBg' => 'bg-amber-500/15 border-amber-500/25', 'iconColor' => 'text-amber-400'],
                    'red' => ['bg' => 'bg-red-500/5 border-red-500/20', 'iconBg' => 'bg-red-500/15 border-red-500/25', 'iconColor' => 'text-red-400'],
                    'sky' => ['bg' => 'bg-sky-500/5 border-sky-500/20', 'iconBg' => 'bg-sky-500/15 border-sky-500/25', 'iconColor' => 'text-sky-400'],
                    'gold' => ['bg' => 'bg-honey/5 border-honey/20', 'iconBg' => 'bg-honey/15 border-honey/25', 'iconColor' => 'text-honey'],
                    default => ['bg' => 'bg-honey/5 border-honey/20', 'iconBg' => 'bg-honey/15 border-honey/25', 'iconColor' => 'text-honey'],
                };
                $iconComponent = $cmd['icon'] instanceof \Filament\Support\Icons\Heroicon ? 'heroicon-' . $cmd['icon']->value : $cmd['icon'];
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
                        <div class="text-[0.95rem] font-bold text-white">{{ $cmd['label'] }}</div>
                        <div class="text-cinnamon mt-0.5 font-mono text-[0.7rem]">{{ $cmd['key'] }}</div>
                    </div>
                </div>

                <div class="text-cinnamon mb-4 flex-1 text-[0.8rem] leading-snug">{{ $cmd['description'] }}</div>

                <div class="flex items-center justify-between gap-2">
                    <div class="text-cinnamon text-[0.7rem]">
                        @if ($lastRunCarbon)
                            <span class="text-cinnamon/70">Last run</span>
                            <span
                                class="text-parchment"
                                title="{{ $lastRunCarbon->format('M j, Y · g:i A') }}"
                            >{{ $lastRunCarbon->diffForHumans() }}</span>
                        @else
                            <span class="text-cinnamon/60">Never run from this UI</span>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="run('{{ $cmd['key'] }}')"
                        wire:loading.attr="disabled"
                        wire:confirm="Run {{ $cmd['label'] }} now?"
                        class="bg-honey/10 text-honey border-honey/25 hover:bg-honey hover:text-warm-black inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold transition-colors disabled:cursor-wait disabled:opacity-50"
                    >
                        <x-heroicon-o-play class="h-3.5 w-3.5" />
                        <span wire:loading.remove wire:target="run('{{ $cmd['key'] }}')">Run Now</span>
                        <span wire:loading wire:target="run('{{ $cmd['key'] }}')">Running…</span>
                    </button>
                </div>
                @if ($taskStatus !== [])
                    <div class="text-cinnamon/80 mt-3 flex flex-wrap gap-x-3 gap-y-1 border-t border-white/10 pt-3 text-[0.68rem]">
                        <span
                            @class([
                                'font-semibold',
                                'text-emerald-400' => ($taskStatus['status'] ?? null) === 'succeeded',
                                'text-red-400' => ($taskStatus['status'] ?? null) === 'failed',
                                'text-amber-400' => ($taskStatus['status'] ?? null) === 'running',
                            ])
                        >{{ ucfirst($taskStatus['status'] ?? 'unknown') }}</span>
                        @if (isset($taskStatus['runtime_seconds']))
                            <span>{{ number_format((float) $taskStatus['runtime_seconds'], 2) }}s</span>
                        @endif
                        @if (($taskStatus['error'] ?? null) !== null)
                            <span
                                class="w-full truncate text-red-300"
                                title="{{ $taskStatus['error'] }}"
                            >{{ $taskStatus['error'] }}</span>
                        @endif
                    </div>
                @endif
            </x-central.card>
        @endforeach
    </div>
</x-filament-panels::page>
