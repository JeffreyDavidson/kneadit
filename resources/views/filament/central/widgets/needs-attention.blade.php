@php $items = $this->getItems(); @endphp

<x-filament-widgets::widget>
    <x-central.card>
        <div class="mb-4 flex items-center justify-between">
            <x-central.eyebrow>Needs Your Attention</x-central.eyebrow>
            @if (count($items))
                <span class="text-[0.7rem] text-(--kn-muted)">{{ count($items) }} {{ \Illuminate\Support\Str::plural('item', count($items)) }}</span>
            @endif
        </div>

        @if (empty($items))
            <div class="flex items-center gap-3 py-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-(--kn-success)/25 bg-(--kn-success-tint)">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-(--kn-success)" />
                </div>
                <div>
                    <div class="text-[0.9rem] font-semibold text-(--kn-ink)">All clear</div>
                    <div class="text-[0.8rem] text-(--kn-muted)">No urgent items right now.</div>
                </div>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($items as $item)
                    @php
                        $tone = match ($item['severity']) {
                            'critical' => ['border' => 'border-(--kn-danger)/25', 'bg' => 'bg-(--kn-danger-tint)', 'iconBg' => 'bg-(--kn-danger-tint) border-(--kn-danger)/25', 'iconColor' => 'text-(--kn-danger)'],
                            'warning' => ['border' => 'border-(--kn-warning)/25', 'bg' => 'bg-(--kn-warning-tint)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-warning)/25', 'iconColor' => 'text-(--kn-warning)'],
                            default => ['border' => 'border-(--kn-border)', 'bg' => 'bg-(--kn-surface-hover)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-honey)/25', 'iconColor' => 'text-(--kn-honey-text)'],
                        };
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg border {{ $tone['border'] }} {{ $tone['bg'] }} hover:border-(--kn-honey)/40 transition-colors no-underline"
                    >
                        <div class="shrink-0 w-10 h-10 rounded-xl {{ $tone['iconBg'] }} border flex items-center justify-center">
                            <x-filament::icon :icon="$item['icon']" class="w-5 h-5 {{ $tone['iconColor'] }}" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[0.9rem] font-semibold text-(--kn-ink)">{{ $item['title'] }}</div>
                            <div class="text-[0.75rem] text-(--kn-muted)">{{ $item['subtitle'] }}</div>
                        </div>
                        <span class="hidden shrink-0 items-center gap-1 text-[0.75rem] font-semibold text-(--kn-honey-text) sm:inline-flex">
                            {{ $item['cta'] }}
                            <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-central.card>
</x-filament-widgets::widget>
