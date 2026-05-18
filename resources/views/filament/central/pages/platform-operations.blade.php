<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-cinnamon text-sm m-0">Manually trigger maintenance jobs that normally run on the cron schedule. Use sparingly — running too often can spam tenants with duplicate emails.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
            @endphp

            <x-central.card class="flex flex-col {{ $tone['bg'] }}">
                <div class="flex items-start gap-3 mb-3">
                    <div class="shrink-0 w-11 h-11 rounded-xl border {{ $tone['iconBg'] }} flex items-center justify-center">
                        <x-dynamic-component :component="$iconComponent" class="w-5 h-5 {{ $tone['iconColor'] }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-bold text-[0.95rem]">{{ $cmd['label'] }}</div>
                        <div class="text-cinnamon text-[0.7rem] font-mono mt-0.5">{{ $cmd['key'] }}</div>
                    </div>
                </div>

                <div class="text-cinnamon text-[0.8rem] leading-snug mb-4 flex-1">
                    {{ $cmd['description'] }}
                </div>

                <div class="flex items-center justify-between gap-2">
                    <div class="text-[0.7rem] text-cinnamon">
                        @if ($lastRunCarbon)
                            <span class="text-cinnamon/70">Last run</span>
                            <span class="text-parchment" title="{{ $lastRunCarbon->format('M j, Y · g:i A') }}">{{ $lastRunCarbon->diffForHumans() }}</span>
                        @else
                            <span class="text-cinnamon/60">Never run from this UI</span>
                        @endif
                    </div>
                    <button type="button"
                        wire:click="run('{{ $cmd['key'] }}')"
                        wire:loading.attr="disabled"
                        wire:confirm="Run {{ $cmd['label'] }} now?"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.75rem] font-semibold bg-honey/10 text-honey border border-honey/25 hover:bg-honey hover:text-warm-black transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                        <x-heroicon-o-play class="w-3.5 h-3.5" />
                        <span wire:loading.remove wire:target="run('{{ $cmd['key'] }}')">Run Now</span>
                        <span wire:loading wire:target="run('{{ $cmd['key'] }}')">Running…</span>
                    </button>
                </div>
            </x-central.card>
        @endforeach
    </div>
</x-filament-panels::page>
