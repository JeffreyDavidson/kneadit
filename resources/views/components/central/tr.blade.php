@props(['highlight' => false, 'border' => true])

<tr {{
    $attributes->class([
        'border-b border-(--kn-border)' => $border,
        'bg-(--kn-surface-hover)' => $highlight,
    ])
}}>
    {{ $slot }}
</tr>
