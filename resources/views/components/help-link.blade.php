@props([
    'to' => null,           // "topic-slug/article-slug" — required
    'label' => 'Learn more', // tooltip + screen-reader label
    'inline' => false,       // true → inline-flex with caption, false → just an icon button
])

@php
    if (! $to) {
        return;
    }

    $href = route('filament.admin.pages.help-center', ['article' => $to]);
@endphp

@if ($inline)
    <a
        href="{{ $href }}"
        title="{{ $label }}"
        class="text-honey inline-flex items-center gap-1 text-[0.75rem] font-medium no-underline hover:text-amber-600"
    >
        <x-heroicon-o-question-mark-circle class="h-3.5 w-3.5" stroke-width="2" />
        {{ $label }}
    </a>
@else
    <a
        href="{{ $href }}"
        title="{{ $label }}"
        class="text-honey/70 hover:text-honey hover:bg-honey/10 inline-flex h-5 w-5 items-center justify-center rounded-full no-underline transition-colors"
    >
        <span class="sr-only">{{ $label }}</span>
        <x-heroicon-o-question-mark-circle class="h-4 w-4" stroke-width="2" />
    </a>
@endif
