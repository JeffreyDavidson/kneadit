@props(['label', 'pct', 'value' => null])

<div {{ $attributes->merge(['style' => 'margin-bottom: 6px;']) }}>
    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--pw-card-text-row);">
        <span>{{ $label }}</span>
        <span>{{ $value ?? "{$pct}%" }}</span>
    </div>
    <div class="pw-bar">
        <div class="pw-bar-fill" style="width: {{ $pct }}%;"></div>
    </div>
</div>
