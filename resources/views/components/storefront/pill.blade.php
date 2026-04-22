@props([
    'tone' => 'subtle',
    'size' => 'sm',
])

@php
    $toneClass = match ($tone) {
        'subtle' => 'bg-warm-500/15 text-warm-500',
        'solid' => 'bg-warm-500 text-warm-900',
        'outlined' => 'bg-warm-500/15 text-warm-400 border border-warm-500/30',
        default => 'bg-warm-500/15 text-warm-500',
    };

    $sizeClass = match ($size) {
        'xs' => 'text-xs px-2 py-1',
        'sm' => 'text-xs px-3 py-1',
        'md' => 'text-sm px-4 py-1.5',
        default => 'text-xs px-3 py-1',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center justify-center rounded-full font-semibold', $toneClass, $sizeClass]) }}>
    {{ $slot }}
</span>
