@props([
    'color' => 'honey',
    'size' => 'md',
    'uppercase' => true,
])

@php
    $colors = [
        'honey' => 'bg-(--kn-honey) text-(--kn-on-honey)',
        'honey-soft' => 'bg-(--kn-warning-tint) text-(--kn-honey-text)',
        'honey-soft-light' => 'bg-(--kn-warning-tint) text-(--kn-ink)',
        'golden' => 'bg-(--kn-honey-hover) text-(--kn-on-honey)',
        'butter' => 'bg-(--kn-surface-hover) text-(--kn-on-honey)',
        'success' => 'bg-(--kn-success-tint) text-(--kn-success)',
        'warning' => 'bg-(--kn-warning-tint) text-(--kn-warning)',
        'danger' => 'bg-(--kn-danger-tint) text-(--kn-danger)',
        'neutral' => 'bg-(--kn-surface-sunken) text-(--kn-honey-text)',
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
