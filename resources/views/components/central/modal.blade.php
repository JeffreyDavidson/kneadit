@props([
    'name',
    'variant' => 'default',
    'maxWidth' => 'max-w-[440px]',
])

@php
    $borderColor = match ($variant) {
        'danger' => 'border-(--kn-danger)/30',
        'success' => 'border-(--kn-success)/30',
        'warning' => 'border-(--kn-warning)/30',
        'info' => 'border-(--kn-info)/30',
        default => 'border-(--kn-honey)/30',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="open = false"
    @click.self="open = false"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-(--kn-espresso)/70 px-4 backdrop-blur-sm"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full {{ $maxWidth }} rounded-xl border {{ $borderColor }} bg-(--kn-surface) shadow-2xl overflow-hidden"
    >
        {{ $slot }}
    </div>
</div>
