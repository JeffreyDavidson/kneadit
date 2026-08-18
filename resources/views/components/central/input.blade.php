@props(['type' => 'text'])

<input
    type="{{ $type }}"
    {{ $attributes->class([
        'w-full bg-espresso border border-honey/12 rounded-lg px-3 py-2 text-parchment text-sm outline-none box-border focus:border-honey transition-colors',
    ]) }}
>
