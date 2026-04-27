@props(['heading', 'icon' => null])

<div {{ $attributes->class('preview-widget') }}>
    <div class="preview-widget-header">
        @if ($icon)
            <span class="pw-icon">{{ $icon }}</span>
        @endif
        <span>{{ $heading }}</span>
    </div>
    <div class="preview-widget-body">
        {{ $slot }}
    </div>
</div>
