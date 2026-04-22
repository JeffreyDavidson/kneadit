@props(['label', 'valueClass' => 'text-[1.5rem] text-parchment'])

<x-central.card padding="p-5">
    <x-central.eyebrow>{{ $label }}</x-central.eyebrow>
    <div {{ $attributes->class(['font-bold mt-1', $valueClass]) }}>{{ $slot }}</div>
</x-central.card>
