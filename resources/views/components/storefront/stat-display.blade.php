@props([
    'value' => null,
    'label',
    'wrapperClass' => 'stat-card text-center',
])

<div class="{{ $wrapperClass }}">
    <span {{ $attributes->class(['block font-display text-3xl md:text-4xl font-bold text-warm-400']) }}>
        {{ $value ?? $slot }}
    </span>
    <span class="text-warm-600 mt-1 block text-xs tracking-[0.2em] uppercase">{{ $label }}</span>
</div>
