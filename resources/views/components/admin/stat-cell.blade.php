@props(['label', 'valueClass' => 'text-2xl font-bold text-brand-900'])

<div {{ $attributes->class(['bg-brand-50 rounded-lg p-3 text-center']) }}>
    <div class="{{ $valueClass }}">{{ $slot }}</div>
    <div class="text-[0.7rem] text-brand-700">{{ $label }}</div>
</div>
