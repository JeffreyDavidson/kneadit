@php
    $heading = $config['heading'] ?? 'Treat Yourself Today';
    $subtext = $config['subtext'] ?? null;
    $buttonText = $config['button_text'] ?? 'Start Your Order';
    $buttonLink = $config['button_link'] ?? 'order';
    $leadTimeHours = $settings->orders->leadTimeHours;
    $storeName = $settings->store->name;
    $heroImage = $settings->branding->heroImage;

    $linkMap = [
        'order' => route('order.create'),
        'menu' => route('storefront.menu'),
        'contact' => route('contact.show'),
    ];
    $href = $linkMap[$buttonLink] ?? route('order.create');

    $ctaImageUrl = $heroImage
        ? Storage::url($heroImage)
        : 'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1920&q=80';
@endphp
<section class="relative py-0 overflow-hidden min-h-[500px]">
    {{-- Background image --}}
    <div class="absolute inset-0">
        <img src="{{ $ctaImageUrl }}" alt="" class="w-full h-full object-cover brightness-[0.3]">
    </div>

    {{-- Gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-r from-warm-900/95 via-warm-900/70 to-warm-900/95"></div>

    {{-- Grain --}}
    <x-storefront.grain-texture opacity="0.04" />

    {{-- Content --}}
    <div class="relative z-10 max-w-3xl mx-auto text-center px-4 py-28">
        {{-- Decorative element --}}
        <div class="flex items-center justify-center gap-4 mb-8">
            <span class="block w-16 h-px bg-warm-500 opacity-40"></span>
            <span class="block w-2 h-2 rounded-full bg-warm-500 opacity-50"></span>
            <span class="block w-16 h-px bg-warm-500 opacity-40"></span>
        </div>

        <p class="font-script text-2xl md:text-3xl mb-4 text-warm-400">Life's too short for store-bought</p>

        <h2 class="font-display text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
            {{ $heading }}
        </h2>

        <p class="text-base md:text-lg mb-10 leading-relaxed max-w-xl mx-auto text-warm-400">
            {{ $subtext ?: "Every order is baked fresh just for you. We just need {$leadTimeHours} hours notice to make it perfect." }}
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <x-storefront.button :href="$href" size="xl">
                {{ $buttonText }}
            </x-storefront.button>
            <x-storefront.button :href="route('storefront.menu')" variant="outline-dark" size="lg">
                See What's Baking
            </x-storefront.button>
        </div>

        {{-- Trust line --}}
        <div class="flex items-center justify-center gap-6 mt-12 text-sm text-warm-500">
            <span class="font-script text-base">Made fresh daily</span>
            <span class="opacity-30">·</span>
            <span class="font-script text-base">Locally sourced</span>
            <span class="opacity-30">·</span>
            <span class="font-script text-base">Baked with love</span>
        </div>
    </div>
</section>
