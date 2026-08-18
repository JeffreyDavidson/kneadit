@props([])

<textarea
    {{ $attributes->class([
        'w-full bg-espresso border border-honey/12 rounded-lg p-3 text-parchment text-[0.9rem] outline-none box-border resize-y font-[inherit] focus:border-honey transition-colors',
    ]) }}
>{{ $slot }}</textarea>
