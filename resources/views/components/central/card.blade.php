@props(['padding' => 'p-6', 'title' => null])

<div {{ $attributes->class(['bg-(--kn-surface) border border-(--kn-border) rounded-xl', $padding]) }}>
    @if ($title)
        <div class="mb-4 text-base font-bold text-(--kn-ink)">{{ $title }}</div>
    @endif
    {{ $slot }}
</div>
