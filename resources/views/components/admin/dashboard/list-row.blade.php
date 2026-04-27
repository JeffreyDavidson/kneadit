@props(['label', 'value' => null, 'dotColor' => null])

<div {{ $attributes->class('pw-row') }}>
    <span>
        @if ($dotColor)
            <span class="pw-dot" style="background: {{ $dotColor }};"></span>
        @endif
        {{ $label }}
    </span>
    @if ($value !== null)
        <span>{{ $value }}</span>
    @endif
</div>
