@props([
    'type' => 'success',
    'variant' => 'dark',
    'dismissAfter' => null,
])

@php
    $styles = match ("{$type}-{$variant}") {
        'success-dark' => 'background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac;',
        'error-dark' => 'background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5;',
        'success-light' => 'background: #d4edda; border: 1px solid #28a745; color: #155724;',
        'error-light' => 'background: #f8d7da; border: 1px solid #dc3545; color: #721c24;',
    };
@endphp

<div
    class="rounded-2xl p-5 mb-6 {{ $type === 'success' ? 'text-center' : '' }}"
    style="{{ $styles }}"
    @if ($dismissAfter)
        x-data="{ visible: true }"
        x-show="visible"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-init="setTimeout(() => visible = false, {{ $dismissAfter }})"
    @endif
>
    {{ $slot }}
</div>
