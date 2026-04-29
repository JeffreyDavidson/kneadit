@php
    use Illuminate\Support\Str;

    $items = $this->getItems();
@endphp

@if (count($items) > 0)
    <div class="col-span-full bg-brand-800 border border-brand-700/60 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-brand-300 text-[0.7rem] uppercase tracking-[0.08em] font-semibold">Needs Your Attention</span>
            <span class="text-brand-400 text-[0.7rem]">{{ count($items) }} {{ Str::plural('item', count($items)) }}</span>
        </div>

        <div class="space-y-2">
            @foreach ($items as $item)
                @php
                    $tone = match ($item['severity']) {
                        'critical' => ['border' => 'border-red-500/25', 'bg' => 'bg-red-500/5', 'iconBg' => 'bg-red-500/15 border-red-500/25', 'iconColor' => 'text-red-400'],
                        'warning' => ['border' => 'border-amber-500/25', 'bg' => 'bg-amber-500/5', 'iconBg' => 'bg-amber-500/15 border-amber-500/25', 'iconColor' => 'text-amber-400'],
                        default => ['border' => 'border-brand-300/20', 'bg' => 'bg-brand-300/5', 'iconBg' => 'bg-brand-300/15 border-brand-300/25', 'iconColor' => 'text-brand-300'],
                    };
                @endphp
                <a href="{{ $item['url'] }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg border {{ $tone['border'] }} {{ $tone['bg'] }} hover:border-brand-300/40 transition-colors no-underline">
                    <div class="shrink-0 w-10 h-10 rounded-xl {{ $tone['iconBg'] }} border flex items-center justify-center">
                        <x-dynamic-component :component="$item['icon']" class="w-5 h-5 {{ $tone['iconColor'] }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-[0.9rem]">{{ $item['title'] }}</div>
                        <div class="text-brand-400 text-[0.75rem]">{{ $item['subtitle'] }}</div>
                    </div>
                    <span class="hidden sm:inline-flex items-center gap-1 text-[0.75rem] font-semibold text-brand-300 shrink-0">
                        {{ $item['cta'] }}
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@else
    <div></div>
@endif
