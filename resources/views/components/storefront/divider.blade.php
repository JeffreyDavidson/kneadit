@props([
    'dark' => false,
    'style' => 'line',
    'width' => 'md',
])

@php
    $widths = [
        'sm' => 'max-w-[80px]',
        'md' => 'max-w-[200px]',
        'full' => 'max-w-full',
    ];
    $wClass = $widths[$width] ?? $widths['md'];
    $lineColor = $dark ? 'var(--warm-700)' : 'var(--warm-300)';
    $accentColor = 'var(--warm-500)';
@endphp

<div class="flex items-center justify-center my-8 {{ $wClass }} mx-auto" {{ $attributes }}>
    @if ($style === 'line')
        <div class="w-full h-px" style="background: linear-gradient(to right, transparent, {{ $accentColor }}, transparent);"></div>
    @elseif ($style === 'dot')
        <div class="flex-1 h-px" style="background: linear-gradient(to right, transparent, {{ $accentColor }});"></div>
        <div class="w-2 h-2 rounded-full mx-3" style="background-color: {{ $accentColor }};"></div>
        <div class="flex-1 h-px" style="background: linear-gradient(to left, transparent, {{ $accentColor }});"></div>
    @elseif ($style === 'ornament')
        <div class="flex-1 h-px" style="background: linear-gradient(to right, transparent, {{ $accentColor }});"></div>
        <div class="mx-4">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 2L12 8L18 10L12 12L10 18L8 12L2 10L8 8L10 2Z" fill="{{ $accentColor }}" opacity="0.8"/>
            </svg>
        </div>
        <div class="flex-1 h-px" style="background: linear-gradient(to left, transparent, {{ $accentColor }});"></div>
    @endif
</div>
