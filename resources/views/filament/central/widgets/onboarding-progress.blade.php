@php
    $stats = $this->getOnboardingStats();
@endphp

<x-filament-widgets::widget>
    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
        <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">
            Onboarding Progress
        </div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #ffffff;">
            {{ $stats['onboarded'] }} <span style="font-size: 1rem; color: #8b6844;">of</span> {{ $stats['total'] }}
        </div>
        <div style="font-size: 0.8rem; color: #faf0d6; margin-top: 0.25rem;">
            bakers fully onboarded
        </div>
        <div style="margin-top: 0.75rem; background: rgba(212,146,12,0.08); border-radius: 9999px; height: 6px; overflow: hidden;">
            <div style="height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #d4920c, #e8b04a); width: {{ $stats['percentage'] }}%;"></div>
        </div>
        <div style="font-size: 0.65rem; color: #d4920c; margin-top: 0.375rem; font-weight: 600;">{{ $stats['percentage'] }}%</div>
    </div>
</x-filament-widgets::widget>
