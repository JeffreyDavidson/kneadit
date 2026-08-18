@php $items = $this->getItems(); @endphp

<x-filament-widgets::widget>
    <x-central.card>
        <div class="mb-4 flex items-center justify-between">
            <x-central.eyebrow>Needs Your Attention</x-central.eyebrow>
            @if (count($items))
                <span class="text-cinnamon text-[0.7rem]">{{ count($items) }} {{ \Illuminate\Support\Str::plural('item', count($items)) }}</span>
            @endif
        </div>

        @if (empty($items))
            <div class="flex items-center gap-3 py-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/15">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-emerald-400" />
                </div>
                <div>
                    <div class="text-[0.9rem] font-semibold text-white">All clear</div>
                    <div class="text-cinnamon text-[0.8rem]">No urgent items right now.</div>
                </div>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($items as $item)
                    @php
                        $tone = match ($item['severity']) {
                            'critical' => ['border' => 'border-red-500/25', 'bg' => 'bg-red-500/5', 'iconBg' => 'bg-red-500/15 border-red-500/25', 'iconColor' => 'text-red-400'],
                            'warning' => ['border' => 'border-amber-500/25', 'bg' => 'bg-amber-500/5', 'iconBg' => 'bg-amber-500/15 border-amber-500/25', 'iconColor' => 'text-amber-400'],
                            default => ['border' => 'border-honey/20', 'bg' => 'bg-honey/5', 'iconBg' => 'bg-honey/15 border-honey/25', 'iconColor' => 'text-honey'],
                        };
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg border {{ $tone['border'] }} {{ $tone['bg'] }} hover:border-honey/40 transition-colors no-underline"
                    >
                        <div class="shrink-0 w-10 h-10 rounded-xl {{ $tone['iconBg'] }} border flex items-center justify-center">
                            <x-dynamic-component :component="$item['icon']" class="w-5 h-5 {{ $tone['iconColor'] }}" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[0.9rem] font-semibold text-white">{{ $item['title'] }}</div>
                            <div class="text-cinnamon text-[0.75rem]">{{ $item['subtitle'] }}</div>
                        </div>
                        <span class="text-honey hidden shrink-0 items-center gap-1 text-[0.75rem] font-semibold sm:inline-flex">
                            {{ $item['cta'] }}
                            <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-central.card>
</x-filament-widgets::widget>
