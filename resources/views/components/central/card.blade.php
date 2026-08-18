@props(['padding' => 'p-6', 'title' => null])

<div {{ $attributes->class(['bg-warm-black border border-honey/12 rounded-xl', $padding]) }}>
    @if ($title)
        <div class="mb-4 text-base font-bold text-white">{{ $title }}</div>
    @endif
    {{ $slot }}
</div>
