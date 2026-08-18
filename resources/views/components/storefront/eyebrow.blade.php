@props([
    'lineOpacity' => '1',
    'align' => 'center',
])

<div
    {{ $attributes->class([
        'flex items-center gap-3',
        'justify-center' => $align === 'center',
    ]) }}
>
    <span class="block w-8 h-px" style="background: var(--warm-500);{{ $lineOpacity !== '1' ? " opacity: {$lineOpacity};" : '' }}"></span>
    <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">{{ $slot }}</span>
    @if ($align === 'center')
        <span class="block w-8 h-px" style="background: var(--warm-500);{{ $lineOpacity !== '1' ? " opacity: {$lineOpacity};" : '' }}"></span>
    @endif
</div>
