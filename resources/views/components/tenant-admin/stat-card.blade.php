@props(['label', 'value', 'tone' => 'brand'])

@php
    $valueClass = match ($tone) {
        'brand' => 'text-brand-300',
        'brand-600' => 'text-(--kn-muted)',
        'danger' => 'text-(--kn-danger)',
        'warning' => 'text-(--kn-warning)',
        'success' => 'text-(--kn-success)',
        default => 'text-brand-300',
    };
@endphp

<div class="border-brand-300/20 rounded-xl border bg-(--kn-surface-sunken) p-4 text-center">
    <div class="text-[1.5rem] font-bold {{ $valueClass }}">{{ $value }}</div>
    <div class="text-cinnamon mt-1 text-[0.75rem] tracking-wide uppercase">{{ $label }}</div>
</div>
