@props(['highlight' => false, 'border' => true])

<tr {{
    $attributes->class([
        'border-b border-honey/8' => $border,
        'bg-honey/5' => $highlight,
    ])
}}>
    {{ $slot }}
</tr>
