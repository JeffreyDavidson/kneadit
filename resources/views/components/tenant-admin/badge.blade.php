@props(['type' => 'info', 'label' => ''])

@php
    $variantClass = match ($type) {
        'danger' => 'bg-(--kn-danger-tint) text-(--kn-danger)',
        'warning' => 'bg-(--kn-warning-tint) text-(--kn-warning)',
        'success' => 'bg-(--kn-success-tint) text-(--kn-success)',
        'info' => 'bg-(--kn-info-tint) text-(--kn-info)',
        default => 'bg-(--kn-info-tint) text-(--kn-info)',
    };
@endphp

<span class="inline-block px-2 py-0.5 rounded-md text-[0.7rem] font-semibold {{ $variantClass }}"> {{ $label }} </span>
