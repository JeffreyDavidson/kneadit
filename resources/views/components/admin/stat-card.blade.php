@props(['label', 'value', 'color' => '#d4a574'])
<div style="background: #fdf8f2; border: 1px solid rgba(212,165,116,0.2); border-radius: 12px; padding: 16px; text-align: center;">
    <div style="font-size: 1.5rem; font-weight: 700; color: {{ $color }};">{{ $value }}</div>
    <div style="font-size: 0.75rem; color: #8b6844; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $label }}</div>
</div>
