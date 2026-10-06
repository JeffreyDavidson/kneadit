@props([
    'title',
    'headerClass' => 'mb-4 flex items-center justify-between',
])

<div {{ $attributes->class(['tenant-admin-card p-6']) }}>
    <div class="{{ $headerClass }}">
        <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">{{ $title }}</div>
        @isset($actions)
            {{ $actions }}
        @endisset
    </div>

    {{ $slot }}
</div>
