@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null])

@php
    $variantClass = match ($variant) {
        'primary' => 'bg-(--kn-honey) text-(--kn-on-honey) border-0 hover:bg-(--kn-honey-hover)',
        'secondary' => 'bg-(--kn-surface-hover) text-(--kn-ink) border border-(--kn-border-control)',
        'warning' => 'bg-(--kn-warning) text-(--kn-on-danger) border-0',
        'success' => 'bg-(--kn-success) text-(--kn-on-danger) border-0',
        'neutral' => 'bg-(--kn-surface-hover) text-(--kn-ink) border border-(--kn-border-control)',
        default => 'bg-(--kn-honey) text-(--kn-on-honey) border-0 hover:bg-(--kn-honey-hover)',
    };

    $sizeClass = match ($size) {
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-2.5 text-sm',
        'lg' => 'px-8 py-3 text-base',
        default => 'px-6 py-2.5 text-sm',
    };

    $classes = ['inline-flex items-center justify-center rounded-lg font-bold cursor-pointer transition-colors no-underline', $variantClass, $sizeClass];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}> {{ $slot }} </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
