@props(['label', 'valueClass' => 'text-parchment font-bold'])

<div class="bg-espresso flex justify-between rounded-lg px-3 py-2">
    <x-central.eyebrow as="span" class="self-center">{{ $label }}</x-central.eyebrow>
    <span {{ $attributes->class([$valueClass]) }}>{{ $slot }}</span>
</div>
