@props([
    'size' => 'md',
    'variant' => 'tinted',
    'inline' => false,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'w-12 h-12',
        'md' => 'w-16 h-16',
        'lg' => 'w-20 h-20',
        'xl' => 'w-24 h-24',
        default => 'w-16 h-16',
    };

    $variantClass = match ($variant) {
        'subtle' => 'bg-warm-500/10 border border-warm-500/20',
        'tinted' => 'bg-warm-500/15 border border-warm-500/30',
        'bold' => 'bg-warm-500/15 border-2 border-warm-500',
        'plain' => 'bg-warm-200',
        default => 'bg-warm-500/15 border border-warm-500/30',
    };

    $displayClass = $inline ? 'inline-flex' : 'flex';
@endphp

<div {{ $attributes->class(['rounded-full items-center justify-center', $displayClass, $sizeClass, $variantClass]) }}>
    {{ $slot }}
</div>
