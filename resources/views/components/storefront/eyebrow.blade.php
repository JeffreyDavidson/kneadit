@props([
    'lineOpacity' => '1',
    'align' => 'center',
])

<div {{
    $attributes->class([
        'flex items-center gap-3',
        'justify-center' => $align === 'center',
    ])
}}>
    <span
        class="block h-px w-8"
        style="background: var(--warm-500);{{ $lineOpacity !== '1' ? " opacity: {$lineOpacity};" : '' }}"
    ></span>
    <span class="text-warm-500 text-xs font-semibold tracking-[0.25em] uppercase">{{ $slot }}</span>
    @if ($align === 'center')
        <span
            class="block h-px w-8"
            style="background: var(--warm-500);{{ $lineOpacity !== '1' ? " opacity: {$lineOpacity};" : '' }}"
        ></span>
    @endif
</div>
