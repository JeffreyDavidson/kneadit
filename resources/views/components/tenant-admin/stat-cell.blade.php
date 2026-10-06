@props(['label', 'valueClass' => 'text-2xl font-bold text-(--kn-ink)'])

<div {{ $attributes->class(['bg-(--kn-surface-sunken) rounded-lg p-3 text-center']) }}>
    <div class="{{ $valueClass }}">{{ $slot }}</div>
    <div class="text-[0.7rem] text-(--kn-ink-2)">{{ $label }}</div>
</div>
