@props([
    'image',
    'imageAlt' => '',
    'imageClass' => '',
    'minHeight' => '55vh',
    'gradient' => 'linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%)',
])

<section class="relative overflow-hidden" style="min-height: {{ $minHeight }};">
    <div class="absolute inset-0">
        <img src="{{ $image }}" alt="{{ $imageAlt }}" class="w-full h-full object-cover {{ $imageClass }}">
    </div>
    <div class="absolute inset-0" style="background: {{ $gradient }};"></div>
    <x-storefront.grain-texture />

    {{ $slot }}
</section>
