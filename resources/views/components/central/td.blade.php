@props([
    'align' => 'left',
    'tone' => 'parchment',
    'padding' => 'py-3 px-4',
])

@php
    $aligns = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'];
    $tones = [
        'white' => 'text-white',
        'parchment' => 'text-parchment',
        'honey' => 'text-honey',
        'cinnamon' => 'text-cinnamon',
    ];
@endphp

<td {{ $attributes->class([$padding, $aligns[$align] ?? $aligns['left'], $tones[$tone] ?? $tones['parchment']]) }}>
    {{ $slot }}
</td>
