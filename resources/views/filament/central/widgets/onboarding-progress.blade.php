@php
    $stats = $this->getOnboardingStats();
@endphp

<x-filament-widgets::widget>
    <x-central.card class="text-center">
        <x-central.eyebrow class="mb-2">Onboarding Progress</x-central.eyebrow>
        <div class="text-[1.75rem] font-bold text-white">
            {{ $stats['onboarded'] }} <span class="text-base text-cinnamon">of</span> {{ $stats['total'] }}
        </div>
        <div class="text-[0.8rem] text-parchment mt-1">
            bakers fully onboarded
        </div>
        <div class="mt-3 bg-honey/8 rounded-full h-1.5 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-honey to-golden" style="width: {{ $stats['percentage'] }}%;"></div>
        </div>
        <div class="text-[0.65rem] text-honey mt-1.5 font-semibold">{{ $stats['percentage'] }}%</div>
    </x-central.card>
</x-filament-widgets::widget>
