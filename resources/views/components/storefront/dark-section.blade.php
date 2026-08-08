@props([
    'id' => null,
    'padding' => 'py-20',
    'radialPosition' => '50% 50%',
    'radialOpacity' => '0.06',
    'showRadial' => true,
])

<section @if ($id) id="{{ $id }}" @endif {{ $attributes->class(["relative overflow-hidden bg-warm-900 {$padding}"]) }}>
    @if ($showRadial)
        <div
            class="absolute inset-0"
            style="background: radial-gradient(ellipse at {{ $radialPosition }}, rgba(212,146,12,{{ $radialOpacity }}), transparent 60%);"
        ></div>
    @endif

    <div class="relative z-10">{{ $slot }}</div>
</section>
