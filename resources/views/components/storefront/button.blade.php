@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'cta',
    'size' => 'md',
    'fullWidth' => false,
    'fontDisplay' => false,
])

@php
    $variantClass = match ($variant) {
        'cta' => 'bg-warm-500 text-warm-900',
        'dark' => 'bg-warm-900 text-warm-100',
        default => 'bg-warm-500 text-warm-900',
    };

    $sizeClass = match ($size) {
        'sm' => 'px-6 py-2.5 text-sm',
        'md' => 'px-8 py-3',
        'lg' => 'px-10 py-4 text-lg',
        'xl' => 'px-12 py-4 text-lg',
        default => 'px-8 py-3',
    };

    $widthClass = $fullWidth ? 'w-full block text-center' : 'inline-flex items-center justify-center';
    $fontClass = $fontDisplay ? 'font-display font-semibold' : 'font-semibold';

    $classes = ['rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg', $widthClass, $sizeClass, $variantClass, $fontClass];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
