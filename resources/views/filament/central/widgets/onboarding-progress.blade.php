@php
    $stats = $this->getOnboardingStats();
    $stuck = $stats['total'] - $stats['onboarded'];
    $tone = match (true) {
        $stats['total'] === 0 => ['border' => 'border-(--kn-border)', 'bg' => 'bg-(--kn-surface-hover)', 'icon' => 'text-(--kn-honey-text)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-honey)/25'],
        $stats['percentage'] >= 75 => ['border' => 'border-(--kn-success)/25', 'bg' => 'bg-(--kn-success-tint)', 'icon' => 'text-(--kn-success)', 'iconBg' => 'bg-(--kn-success-tint) border-(--kn-success)/25'],
        $stats['percentage'] >= 30 => ['border' => 'border-(--kn-warning)/25', 'bg' => 'bg-(--kn-warning-tint)', 'icon' => 'text-(--kn-warning)', 'iconBg' => 'bg-(--kn-warning-tint) border-(--kn-warning)/25'],
        default => ['border' => 'border-(--kn-danger)/25', 'bg' => 'bg-(--kn-danger-tint)', 'icon' => 'text-(--kn-danger)', 'iconBg' => 'bg-(--kn-danger-tint) border-(--kn-danger)/25'],
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
                <div class="text-[1.05rem] leading-tight font-bold text-(--kn-ink)">
                    {{ $stats['onboarded'] }}
                    <span class="font-normal text-(--kn-muted)">of</span> {{ $stats['total'] }}
                    <span class="font-normal text-(--kn-muted)">bakeries fully onboarded</span>
                </div>
                @if ($stuck > 0)
                    <div class="mt-1.5 text-[0.8rem] text-(--kn-muted)">
                        {{ $stuck }} {{ \Illuminate\Support\Str::plural('bakery', $stuck) }} still working through setup
                    </div>
                @endif
            </div>

            <div class="flex min-w-65 flex-1 items-center gap-3">
                <div class="h-2 flex-1 overflow-hidden rounded-full bg-(--kn-surface-sunken)">
                    <div
                        class="h-full rounded-full bg-linear-to-r from-(--kn-honey) to-(--kn-honey-hover) transition-all"
                        style="width: {{ $stats['percentage'] }}%;"
                    ></div>
                </div>
                <span class="{{ $tone['icon'] }} text-[0.85rem] font-bold tabular-nums shrink-0">{{ $stats['percentage'] }}%</span>
            </div>

            <a
                href="{{ $trackerUrl }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-3 py-1.5 text-[0.75rem] font-semibold text-(--kn-honey-text) no-underline transition-colors hover:bg-(--kn-honey) hover:text-(--kn-on-honey)"
            >
                Open Tracker
                <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
            </a>
        </div>
    </x-central.card>
</x-filament-widgets::widget>
