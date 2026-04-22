@props([
    'size' => 'md',
    'tone' => 'warm',
])

@php
    $sizeClass = match ($size) {
        'md' => 'text-[5rem] leading-[0.6]',
        'lg' => 'text-[8rem] leading-[0.5]',
        default => 'text-[5rem] leading-[0.6]',
    };

    $toneClass = match ($tone) {
        'warm' => 'text-warm-500 opacity-15',
        'warm-faint' => 'text-warm-500 opacity-[0.12]',
        'warm-muted' => 'text-warm-300 opacity-30',
        default => 'text-warm-500 opacity-15',
    };
@endphp

<div {{ $attributes->class(['font-display font-bold', $sizeClass, $toneClass]) }} aria-hidden="true">&ldquo;</div>
