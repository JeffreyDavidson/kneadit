@props([
    'align' => 'left',
    'tone' => 'parchment',
    'padding' => 'py-3 px-4',
])

@php
    $aligns = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'];
    $tones = [
        'white' => 'text-(--kn-ink)',
        'parchment' => 'text-(--kn-ink)',
        'honey' => 'text-(--kn-honey-text)',
        'cinnamon' => 'text-(--kn-muted)',
    ];
@endphp

<td {{ $attributes->class([$padding, $aligns[$align] ?? $aligns['left'], $tones[$tone] ?? $tones['parchment']]) }}>
    {{ $slot }}
</td>
