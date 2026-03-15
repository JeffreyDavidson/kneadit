@props(['type' => 'info', 'label' => ''])
@php
    $colors = [
        'danger' => ['bg' => '#fde8e8', 'text' => '#c53030'],
        'warning' => ['bg' => '#fef3cd', 'text' => '#b45309'],
        'success' => ['bg' => '#d4edda', 'text' => '#276749'],
        'info' => ['bg' => '#e8f0fe', 'text' => '#2b6cb0'],
    ];
    $c = $colors[$type] ?? $colors['info'];
@endphp
<span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; background: {{ $c['bg'] }}; color: {{ $c['text'] }};">
    {{ $label }}
</span>
