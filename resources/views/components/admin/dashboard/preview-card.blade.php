@props(['heading', 'icon' => null])

<div {{ $attributes->class('preview-widget') }}>
    <div class="preview-widget-header">
        @if ($icon)
            <span class="pw-icon">
                @if (is_string($icon) && str_starts_with($icon, 'heroicon-'))
                    <x-filament::icon :icon="$icon" class="w-4 h-4" />
                @else
                    {{ $icon }}
                @endif
            </span>
        @endif
        <span>{{ $heading }}</span>
    </div>
    <div class="preview-widget-body">
        {{ $slot }}
    </div>
</div>
