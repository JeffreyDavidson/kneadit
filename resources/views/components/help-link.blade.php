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
    <a href="{{ $href }}" title="{{ $label }}"
        class="inline-flex items-center gap-1 text-honey hover:text-amber-600 text-[0.75rem] font-medium no-underline">
        <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5" stroke-width="2" />
        {{ $label }}
    </a>
@else
    <a href="{{ $href }}" title="{{ $label }}"
        class="inline-flex items-center justify-center w-5 h-5 rounded-full text-honey/70 hover:text-honey hover:bg-honey/10 transition-colors no-underline">
        <span class="sr-only">{{ $label }}</span>
        <x-heroicon-o-question-mark-circle class="w-4 h-4" stroke-width="2" />
    </a>
@endif
