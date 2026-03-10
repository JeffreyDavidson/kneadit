@php
    $stats = $this->getOnboardingStats();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div style="text-align: center; padding: 8px 0;">
            <div style="font-size: 0.75rem; font-weight: 500; color: #e8b04a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                Onboarding Progress
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #faf0d6;">
                {{ $stats['onboarded'] }} <span style="font-size: 1rem; color: #e8b04a;">of</span> {{ $stats['total'] }}
            </div>
            <div style="font-size: 0.8rem; color: #d4920c; margin-top: 4px;">
                bakers fully onboarded
            </div>
            <div style="margin-top: 12px; background: #2a1f18; border-radius: 9999px; height: 8px; overflow: hidden;">
                <div style="height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #d4920c, #e8b04a); width: {{ $stats['percentage'] }}%; transition: width 0.5s;"></div>
            </div>
            <div style="font-size: 0.7rem; color: #e8b04a; margin-top: 4px;">{{ $stats['percentage'] }}%</div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
