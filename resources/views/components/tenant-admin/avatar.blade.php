@props(['name' => '', 'size' => 'md'])
@php
    $sizes = ['sm' => '28px', 'md' => '36px', 'lg' => '48px'];
    $fontSizes = ['sm' => '0.65rem', 'md' => '0.8rem', 'lg' => '1rem'];
    $dim = $sizes[$size] ?? $sizes['md'];
    $fs = $fontSizes[$size] ?? $fontSizes['md'];
    $initials = collect(explode(' ', $name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp
<div style="width: {{ $dim }}; height: {{ $dim }}; border-radius: 50%; background: #d4a574; color: white; display: flex; align-items: center; justify-content: center; font-size: {{ $fs }}; font-weight: 600; flex-shrink: 0;">
    {{ $initials }}
</div>
