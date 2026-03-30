@props([
    'bg' => 'light',
    'padding' => 'lg',
    'maxWidth' => '7xl',
    'id' => null,
])

@php
    $bgStyles = [
        'light' => 'background-color: var(--warm-100);',
        'cream' => 'background-color: var(--warm-200);',
        'dark' => 'background-color: var(--warm-900);',
        'white' => 'background-color: #ffffff;',
    ];

    $paddings = [
        'sm' => 'py-8 md:py-12',
        'md' => 'py-12 md:py-16',
        'lg' => 'py-16 md:py-20',
        'xl' => 'py-20 md:py-28',
    ];

    $maxWidths = [
        '3xl' => 'max-w-3xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
        'full' => 'max-w-full',
    ];
@endphp

<section
    @if ($id) id="{{ $id }}" @endif
    style="{{ $bgStyles[$bg] ?? $bgStyles['light'] }}"
    {{ $attributes->class([$paddings[$padding] ?? $paddings['lg']]) }}
>
    <div class="{{ $maxWidths[$maxWidth] ?? $maxWidths['7xl'] }} mx-auto px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
</section>
