@php
    $stats = $this->getOnboardingStats();
    $stuck = $stats['total'] - $stats['onboarded'];
    $tone = match (true) {
        $stats['total'] === 0 => ['border' => 'border-honey/20', 'bg' => 'bg-honey/5', 'icon' => 'text-honey', 'iconBg' => 'bg-honey/15 border-honey/25'],
        $stats['percentage'] >= 75 => ['border' => 'border-emerald-500/25', 'bg' => 'bg-emerald-500/5', 'icon' => 'text-emerald-400', 'iconBg' => 'bg-emerald-500/15 border-emerald-500/25'],
        $stats['percentage'] >= 30 => ['border' => 'border-amber-500/25', 'bg' => 'bg-amber-500/5', 'icon' => 'text-amber-400', 'iconBg' => 'bg-amber-500/15 border-amber-500/25'],
        default => ['border' => 'border-red-500/25', 'bg' => 'bg-red-500/5', 'icon' => 'text-red-400', 'iconBg' => 'bg-red-500/15 border-red-500/25'],
    };
    $trackerUrl = \App\Filament\Central\Pages\OnboardingTracker::getUrl();
@endphp

<x-filament-widgets::widget>
    <x-central.card class="{{ $tone['bg'] }} {{ $tone['border'] }}">
        <div class="flex flex-wrap items-center gap-5">
            <div class="w-11 h-11 rounded-xl {{ $tone['iconBg'] }} border flex items-center justify-center shrink-0">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 {{ $tone['icon'] }}" />
            </div>

            <div class="min-w-60 shrink-0">
                <x-central.eyebrow class="mb-1.5">Onboarding Progress</x-central.eyebrow>
                <div class="text-[1.05rem] leading-tight font-bold text-white">
                    {{ $stats['onboarded'] }} <span class="text-cinnamon font-normal">of</span> {{ $stats['total'] }}
                    <span class="text-cinnamon font-normal">bakeries fully onboarded</span>
                </div>
                @if ($stuck > 0)
                    <div class="text-cinnamon mt-1.5 text-[0.8rem]">
                        {{ $stuck }} {{ \Illuminate\Support\Str::plural('bakery', $stuck) }} still working through setup
                    </div>
                @endif
            </div>

            <div class="flex min-w-65 flex-1 items-center gap-3">
                <div class="bg-espresso h-2 flex-1 overflow-hidden rounded-full">
                    <div
                        class="from-honey to-golden h-full rounded-full bg-linear-to-r transition-all"
                        style="width: {{ $stats['percentage'] }}%;"
                    ></div>
                </div>
                <span class="{{ $tone['icon'] }} text-[0.85rem] font-bold tabular-nums shrink-0">{{ $stats['percentage'] }}%</span>
            </div>

            <a
                href="{{ $trackerUrl }}"
                class="bg-honey/10 text-honey border-honey/25 hover:bg-honey hover:text-warm-black inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold no-underline transition-colors"
            >
                Open Tracker
                <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
            </a>
        </div>
    </x-central.card>
</x-filament-widgets::widget>
