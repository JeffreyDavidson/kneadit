@props([
    'tone' => 'light',
])

@php
    $lineClass = match ($tone) {
        'light' => 'bg-warm-300',
        'dark' => 'bg-warm-700/25',
        default => 'bg-warm-300',
    };
@endphp

<div {{ $attributes->class(['flex items-center gap-6']) }}>
    <div class="flex-1 h-px {{ $lineClass }}"></div>
    <span class="uppercase tracking-[0.25em] text-xs font-semibold whitespace-nowrap text-warm-500">
        {{ $slot }}
    </span>
    <div class="flex-1 h-px {{ $lineClass }}"></div>
</div>
