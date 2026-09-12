@props(['heading', 'icon' => null])

<div {{ $attributes->class('preview-widget') }}>
    <div class="preview-widget-header">
        <div class="preview-widget-title">
            @if ($icon)
                @if (is_string($icon) && str_starts_with($icon, 'heroicon-'))
                    <x-filament::icon :icon="$icon" class="pw-icon" />
                @else
                    <span class="pw-icon">{{ $icon }}</span>
                @endif
            @endif
            <span>{{ $heading }}</span>
        </div>
        @isset($actions)
            <div>{{ $actions }}</div>
        @endisset
    </div>
    <div class="preview-widget-body">{{ $slot }}</div>
</div>
