@props(['label', 'value', 'tone' => 'brand'])

@php
    $valueClass = match ($tone) {
        'brand' => 'text-brand-300',
        'brand-600' => 'text-brand-600',
        'danger' => 'text-red-500',
        'warning' => 'text-amber-500',
        'success' => 'text-emerald-500',
        default => 'text-brand-300',
    };
@endphp

<div class="bg-brand-50 border-brand-300/20 rounded-xl border p-4 text-center">
    <div class="text-[1.5rem] font-bold {{ $valueClass }}">{{ $value }}</div>
    <div class="text-cinnamon mt-1 text-[0.75rem] tracking-wide uppercase">{{ $label }}</div>
</div>
