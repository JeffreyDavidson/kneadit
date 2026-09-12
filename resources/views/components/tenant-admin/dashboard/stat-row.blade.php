@props(['label', 'value'])

<div {{ $attributes->class('pw-stat') }}>
    <span class="pw-stat-label">{{ $label }}</span>
    <span class="pw-stat-value">{{ $value }}</span>
</div>
