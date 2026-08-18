@props(['type' => 'info', 'label' => ''])

@php
    $variantClass = match ($type) {
        'danger' => 'bg-red-100 text-red-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'info' => 'bg-blue-100 text-blue-700',
        default => 'bg-blue-100 text-blue-700',
    };
@endphp

<span class="inline-block px-2 py-0.5 rounded-md text-[0.7rem] font-semibold {{ $variantClass }}"> {{ $label }} </span>
