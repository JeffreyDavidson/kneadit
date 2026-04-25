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
        <div class="flex items-center gap-4 flex-wrap">
            <div class="w-11 h-11 rounded-xl {{ $tone['iconBg'] }} border flex items-center justify-center shrink-0">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 {{ $tone['icon'] }}" />
            </div>

            <div class="flex-1 min-w-[240px]">
                <x-central.eyebrow class="mb-1">Onboarding Progress</x-central.eyebrow>
                <div class="text-white font-bold text-[1.05rem]">
                    {{ $stats['onboarded'] }} <span class="text-cinnamon font-normal">of</span> {{ $stats['total'] }} <span class="text-cinnamon font-normal">bakeries fully onboarded</span>
                </div>
                @if ($stuck > 0)
                    <div class="text-cinnamon text-[0.8rem] mt-1">
                        {{ $stuck }} {{ \Illuminate\Support\Str::plural('bakery', $stuck) }} still working through setup
                    </div>
                @endif
            </div>

            <div class="flex flex-col items-end gap-2 min-w-[180px]">
                <div class="w-full">
                    <div class="flex items-center justify-end gap-2 mb-1">
                        <span class="{{ $tone['icon'] }} text-[0.85rem] font-bold tabular-nums">{{ $stats['percentage'] }}%</span>
                    </div>
                    <div class="bg-espresso rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-honey to-golden transition-all" style="width: {{ $stats['percentage'] }}%;"></div>
                    </div>
                </div>
                <a href="{{ $trackerUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.75rem] font-semibold bg-honey/10 text-honey border border-honey/25 hover:bg-honey hover:text-warm-black transition-colors no-underline">
                    Open Tracker
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </x-central.card>
</x-filament-widgets::widget>
