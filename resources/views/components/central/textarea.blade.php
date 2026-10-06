@props([])

<textarea
    {{
        $attributes->class([
            'w-full bg-(--kn-surface-sunken) border border-(--kn-border) rounded-lg p-3 text-(--kn-ink) text-[0.9rem] outline-none box-border resize-y font-[inherit] focus:border-(--kn-honey-text) transition-colors',
        ])
    }}
>{{ $slot }}</textarea>