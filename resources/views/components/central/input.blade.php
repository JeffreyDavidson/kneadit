@props(['type' => 'text'])

<input
    type="{{ $type }}"
    {{
        $attributes->class([
            'w-full bg-(--kn-surface-sunken) border border-(--kn-border) rounded-lg px-3 py-2 text-(--kn-ink) text-sm outline-none box-border focus:border-(--kn-honey-text) transition-colors',
        ])
    }}
/>
