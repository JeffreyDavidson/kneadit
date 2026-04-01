@props([
    'name',
    'size' => 'md',
    'dark' => false,
])

@php
    $sizes = [
        'sm' => 'w-7 h-7 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-lg',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $initial = strtoupper(substr($name, 0, 1));
    $bgColor = $dark ? 'rgba(212,146,12,0.3)' : 'var(--warm-200)';
    $textColor = $dark ? 'var(--warm-400)' : 'var(--warm-600)';
@endphp

<div
    {{ $attributes->class([$sizeClasses, 'rounded-full flex items-center justify-center font-display font-bold']) }}
    style="background: {{ $bgColor }}; color: {{ $textColor }};"
>
    {{ $initial }}
</div>
