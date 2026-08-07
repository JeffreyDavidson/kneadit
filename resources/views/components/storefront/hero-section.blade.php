@props([
    'image',
    'imageAlt' => '',
    'imageClass' => '',
    'minHeight' => '55vh',
    {{-- Stronger dark coverage now that the grain overlay has been removed.
         Grain previously masked the legibility problem on top of low-contrast
         text; a heavier gradient gives content reliable contrast across any
         photo without the noise. --}}
    'gradient' => 'linear-gradient(to bottom, rgba(28,20,16,0.55) 0%, rgba(28,20,16,0.78) 60%, rgba(28,20,16,0.95) 100%)',
])

<section class="relative overflow-hidden" style="min-height: {{ $minHeight }};">
    <div class="absolute inset-0">
        <img src="{{ $image }}" alt="{{ $imageAlt }}" class="w-full h-full object-cover {{ $imageClass }}" />
    </div>
    <div class="absolute inset-0" style="background: {{ $gradient }};"></div>

    {{ $slot }}
</section>
