@props([
    'name',
    'variant' => 'default',
    'maxWidth' => 'max-w-[440px]',
])

@php
    $borderColor = match ($variant) {
        'danger' => 'border-red-500/30',
        'success' => 'border-emerald-500/30',
        'warning' => 'border-amber-500/30',
        'info' => 'border-sky-500/30',
        default => 'border-honey/30',
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
    class="fixed inset-0 z-[9999] flex items-center justify-center px-4 bg-black/70 backdrop-blur-sm"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full {{ $maxWidth }} rounded-xl border {{ $borderColor }} bg-warm-black shadow-2xl overflow-hidden"
    >
        {{ $slot }}
    </div>
</div>
