@props(['type' => 'text'])

<input
    type="{{ $type }}"
    {{
        $attributes->class([
            'w-full px-3 py-2 border border-(--kn-border-control) bg-(--kn-surface) rounded-lg text-[0.9rem] text-(--kn-ink) outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-300/15 transition-colors',
        ])
    }}
/>
