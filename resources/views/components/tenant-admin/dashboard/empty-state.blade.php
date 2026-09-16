@props([
    'icon' => 'heroicon-o-sparkles',
    'title',
    'copy' => null,
])

<div {{ $attributes->class('pw-empty-state') }}>
    <x-filament::icon :icon="$icon" aria-hidden="true" />
    <div class="pw-empty-state-title">{{ $title }}</div>
    @if ($copy)
        <div class="pw-empty-state-copy">{{ $copy }}</div>
    @endif
</div>
