@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'fullWidth' => false,
])

@php
    $tag = $href ? 'a' : 'button';

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm md:text-base',
        'lg' => 'px-8 py-4 text-base md:text-lg',
    ];

    $variantStyles = [
        'primary' => 'background-color: var(--warm-500); color: #ffffff;',
        'secondary' => 'background-color: transparent; color: var(--warm-700); border: 2px solid var(--warm-500);',
        'ghost' => 'background-color: transparent; color: var(--warm-700);',
        'dark' => 'background-color: var(--warm-900); color: var(--warm-50);',
    ];

    $baseClasses = 'inline-flex items-center justify-center gap-2 font-body font-semibold rounded-lg transition-all duration-300 cursor-pointer';
    $hoverClasses = $variant === 'ghost' ? '' : 'hover:scale-[1.02] hover:shadow-lg';
    $widthClass = $fullWidth ? 'w-full' : '';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    style="{{ $variantStyles[$variant] ?? $variantStyles['primary'] }}"
    {{ $attributes->class([$baseClasses, $hoverClasses, $sizes[$size] ?? $sizes['md'], $widthClass]) }}
>
    {{ $slot }}

    @if($icon === 'arrow')
        <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
    @endif
</{{ $tag }}>
