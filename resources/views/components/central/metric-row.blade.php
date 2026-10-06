@props(['label', 'valueClass' => 'text-(--kn-ink) font-bold'])

<div class="flex justify-between rounded-lg bg-(--kn-surface-sunken) px-3 py-2">
    <x-central.eyebrow as="span" class="self-center">{{ $label }}</x-central.eyebrow>
    <span {{ $attributes->class([$valueClass]) }}>{{ $slot }}</span>
</div>
