@props(['variant' => 'primary', 'type' => 'button'])

@php
    $base = 'inline-flex items-center justify-center rounded-lg font-bold cursor-pointer transition-colors text-sm';
    $variants = [
        'primary' => 'bg-honey text-warm-black border-0 px-6 py-2.5 hover:bg-golden',
        'secondary' => 'bg-espresso text-honey border border-honey/12 px-4 py-2 hover:border-honey',
    ];
    $variantClasses = $variants[$variant] ?? $variants['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->class([$base, $variantClasses]) }}>
    {{ $slot }}
</button>
