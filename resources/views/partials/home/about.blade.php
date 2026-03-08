@php
    $aboutUs = \App\Models\Setting::get('about_us_text');
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp
@if($aboutUs)
<x-storefront.divider style="line" width="full" />

<x-storefront.section bg="white" padding="xl" maxWidth="5xl">
    <x-storefront.section-header
        eyebrow="Our Story"
        :title="$storeName"
        align="center"
    />

    <div class="relative max-w-3xl mx-auto text-center">
        {{-- Decorative quote mark --}}
        <span class="block font-script text-7xl md:text-8xl leading-none mb-4" style="color: var(--warm-500); opacity: 0.2;">&ldquo;</span>

        <p class="font-body text-lg md:text-xl leading-relaxed italic mb-8" style="color: var(--warm-700);">
            {{ $aboutUs }}
        </p>

        <x-storefront.divider style="dot" width="sm" />

        <x-storefront.button href="{{ route('storefront.about') }}" variant="ghost" icon="arrow" class="group">
            Learn more about {{ $storeName }}
        </x-storefront.button>
    </div>
</x-storefront.section>
@endif
