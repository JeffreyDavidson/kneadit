@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'align' => 'left',
    'dark' => false,
    'titleSize' => 'lg',
])

@php
    $alignClass = $align === 'center' ? 'text-center' : 'text-left';

    $titleSizes = [
        'md' => 'text-2xl md:text-3xl',
        'lg' => 'text-3xl md:text-4xl lg:text-5xl',
        'xl' => 'text-4xl md:text-5xl lg:text-6xl',
    ];

    $titleColor = $dark ? 'color: var(--warm-50);' : 'color: var(--warm-900);';
    $subtitleColor = $dark ? 'color: var(--warm-300);' : 'color: var(--warm-600);';
@endphp

<div class="{{ $alignClass }} mb-12 {{ $align === 'center' ? 'max-w-3xl mx-auto' : '' }}">
    @if ($eyebrow)
        <span
            class="inline-block text-xs md:text-sm font-body font-semibold uppercase tracking-[0.2em] mb-3"
            style="color: var(--warm-500);"
        >{{ $eyebrow }}</span>
    @endif

    <h2
        class="font-display font-bold leading-tight {{ $titleSizes[$titleSize] ?? $titleSizes['lg'] }}"
        style="{{ $titleColor }}"
    >{{ $title }}</h2>

    @if ($subtitle)
        <p
            class="mt-4 text-base md:text-lg font-body leading-relaxed max-w-2xl {{ $align === 'center' ? 'mx-auto' : '' }}"
            style="{{ $subtitleColor }}"
        >{{ $subtitle }}</p>
    @endif
</div>
