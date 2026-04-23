@props([
    'value' => null,
    'label',
    'wrapperClass' => 'stat-card text-center',
])

<div class="{{ $wrapperClass }}">
    <span {{ $attributes->class(['block font-display text-3xl md:text-4xl font-bold text-warm-400']) }}>
        {{ $value ?? $slot }}
    </span>
    <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">{{ $label }}</span>
</div>
