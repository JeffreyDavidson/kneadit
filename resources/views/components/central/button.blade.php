@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $base = 'inline-flex items-center justify-center rounded-lg font-bold cursor-pointer transition-colors text-sm no-underline';
    $variants = [
        'primary' => 'bg-honey text-warm-black border-0 px-6 py-2.5 hover:bg-golden',
        'secondary' => 'bg-espresso text-honey border border-honey/12 px-4 py-2 hover:border-honey',
        'warning' => 'bg-amber-800 text-amber-200 border-0 px-4 py-2',
        'success' => 'bg-emerald-800 text-emerald-300 border-0 px-4 py-2',
        'neutral' => 'bg-gray-700 text-gray-300 border-0 px-4 py-2',
    ];
    $variantClasses = $variants[$variant] ?? $variants['primary'];
    $classes = "{$base} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </button>
@endif
