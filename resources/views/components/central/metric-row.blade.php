@props(['label', 'valueClass' => 'text-parchment font-bold'])

<div class="flex justify-between py-2 px-3 bg-espresso rounded-lg">
    <x-central.eyebrow as="span" class="self-center">{{ $label }}</x-central.eyebrow>
    <span {{ $attributes->class([$valueClass]) }}>{{ $slot }}</span>
</div>
