@props([
    'color' => 'honey',
    'size' => 'md',
    'uppercase' => true,
])

@php
    $colors = [
        'honey' => 'bg-honey text-warm-black',
        'honey-soft' => 'bg-honey/10 text-honey',
        'honey-soft-light' => 'bg-honey/10 text-parchment',
        'golden' => 'bg-golden text-warm-black',
        'butter' => 'bg-butter text-warm-black',
        'success' => 'bg-emerald-500/15 text-emerald-500',
        'warning' => 'bg-amber-500/15 text-amber-500',
        'danger' => 'bg-red-500/15 text-red-500',
        'neutral' => 'bg-espresso text-honey',
    ];
    $sizes = [
        'sm' => 'text-[0.6rem] px-2 py-0.5',
        'md' => 'text-[0.7rem] px-2.5 py-1',
        'lg' => 'text-[0.8rem] px-3 py-1.5',
    ];
    $base = 'inline-block rounded-full font-semibold whitespace-nowrap';
    $tracking = $uppercase ? 'uppercase tracking-[0.1em]' : '';
@endphp

<span {{ $attributes->class([$base, $colors[$color] ?? $colors['honey'], $sizes[$size] ?? $sizes['md'], $tracking]) }}>
    {{ $slot }}
</span>
