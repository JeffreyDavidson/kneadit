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
    <span class="text-warm-500 text-xs font-semibold tracking-[0.25em] whitespace-nowrap uppercase"> {{ $slot }} </span>
    <div class="flex-1 h-px {{ $lineClass }}"></div>
</div>
