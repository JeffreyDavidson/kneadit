@props(['padding' => 'p-6', 'title' => null])

<div {{ $attributes->class(['bg-warm-black border border-honey/12 rounded-xl', $padding]) }}>
    @if ($title)
        <div class="text-white font-bold text-base mb-4">{{ $title }}</div>
    @endif
    {{ $slot }}
</div>
