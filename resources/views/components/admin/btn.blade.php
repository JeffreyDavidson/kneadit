@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'size' => 'md', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-brand-300 text-white border-0',
        'secondary' => 'bg-brand-50 text-brand-900 border border-brand-300/30',
        'danger' => 'bg-red-700 text-white border-0',
        'ghost' => 'bg-white/20 text-white border-0 hover:bg-white/30',
    ];
    $paddings = [
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-4 py-2 text-[0.85rem]',
        'lg' => 'px-5 py-2.5 text-[0.95rem]',
    ];
    $base = 'inline-flex items-center gap-1 rounded-lg font-semibold no-underline cursor-pointer';
    $classes = "{$base} {$variants[$variant]} {$paddings[$size]}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        @if ($icon)<span>{{ $icon }}</span>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        @if ($icon)<span>{{ $icon }}</span>@endif{{ $slot }}
    </button>
@endif
