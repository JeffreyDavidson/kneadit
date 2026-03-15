@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'size' => 'md'])
@php
    $variants = [
        'primary' => 'background: #d4a574; color: white;',
        'secondary' => 'background: #fdf8f2; color: #3d2314; border: 1px solid rgba(212,165,116,0.3);',
        'danger' => 'background: #c53030; color: white;',
    ];
    $paddings = ['sm' => '4px 10px', 'md' => '8px 16px', 'lg' => '10px 20px'];
    $fontSizes = ['sm' => '0.75rem', 'md' => '0.85rem', 'lg' => '0.95rem'];
    $style = ($variants[$variant] ?? $variants['primary']) . ' border-radius: 8px; padding: ' . ($paddings[$size] ?? $paddings['md']) . '; font-size: ' . ($fontSizes[$size] ?? $fontSizes['md']) . '; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; border: none;';
@endphp
@if($href)
    <a href="{{ $href }}" style="{{ $style }}" {{ $attributes }}>@if($icon)<span>{{ $icon }}</span>@endif{{ $slot }}</a>
@else
    <button style="{{ $style }}" {{ $attributes }}>@if($icon)<span>{{ $icon }}</span>@endif{{ $slot }}</button>
@endif
