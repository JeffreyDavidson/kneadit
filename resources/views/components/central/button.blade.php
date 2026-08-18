@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null])

@php
    $variantClass = match ($variant) {
        'primary' => 'bg-honey text-warm-black border-0 hover:bg-golden',
        'secondary' => 'bg-espresso text-honey border border-honey/12 hover:border-honey',
        'warning' => 'bg-amber-800 text-amber-200 border-0',
        'success' => 'bg-emerald-800 text-emerald-300 border-0',
        'neutral' => 'bg-gray-700 text-gray-300 border-0',
        default => 'bg-honey text-warm-black border-0 hover:bg-golden',
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
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
