@props(['settings'])

@php
    $announcementText = $settings->engagement->announcementText;
    $announcementType = $settings->engagement->announcementType;
    $announcementDismissKey = $announcementText
        ? 'announcement_dismissed_'.hash('xxh128', $announcementText)
        : null;
    $announcementClass = match ($announcementType) {
        'warning' => 'bg-yellow-100 text-yellow-800 border-b-2 border-yellow-500',
        'success' => 'bg-green-100 text-green-800 border-b-2 border-green-600',
        'holiday' => 'bg-gradient-to-br from-red-700 to-green-800 text-white border-b-2 border-yellow-400',
        default => 'bg-warm-200 text-warm-900 border-b-2 border-warm-500',
    };
@endphp

@if ($settings->engagement->announcementEnabled && $announcementDismissKey)
    <div
        x-data="{ show: ! localStorage.getItem('{{ $announcementDismissKey }}') }"
        x-show="show"
        x-transition
        class="relative px-4 py-3 text-center text-sm font-medium {{ $announcementClass }}"
    >
        <span>{{ $announcementText }}</span>
        <button
            @click="show = false; localStorage.setItem('{{ $announcementDismissKey }}', '1')"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-lg leading-none opacity-70 hover:opacity-100"
            aria-label="Dismiss"
        >
            &times;
        </button>
    </div>
@endif
