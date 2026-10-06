@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'size' => 'md', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-(--kn-honey) text-(--kn-on-honey) hover:bg-(--kn-honey-hover) border-0',
        'secondary' => 'bg-(--kn-surface-hover) text-(--kn-ink) border border-(--kn-border-control)',
        'danger' => 'bg-(--kn-danger) text-(--kn-on-danger) border-0',
        'ghost' => 'text-(--kn-ink) border-0 hover:bg-(--kn-surface-hover)',
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
        @if ($icon)
            @if (is_string($icon) && str_starts_with($icon, 'heroicon-'))
                <x-filament::icon :icon="$icon" class="h-4 w-4" />
            @else
                <span>{{ $icon }}</span>
            @endif
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        @if ($icon)
            @if (is_string($icon) && str_starts_with($icon, 'heroicon-'))
                <x-filament::icon :icon="$icon" class="h-4 w-4" />
            @else
                <span>{{ $icon }}</span>
            @endif
        @endif
        {{ $slot }}
    </button>
@endif
