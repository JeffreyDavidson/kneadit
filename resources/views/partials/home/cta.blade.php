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
<section class="relative min-h-[500px] overflow-hidden py-0">
    {{-- Background image --}}
    <div class="absolute inset-0">
        <img src="{{ $ctaImageUrl }}" alt="" class="h-full w-full object-cover brightness-[0.3]" />
    </div>

    {{-- Gradient overlay --}}
    <div class="from-warm-900/95 via-warm-900/70 to-warm-900/95 absolute inset-0 bg-gradient-to-r"></div>

    {{-- Content --}}
    <div class="relative z-10 mx-auto max-w-3xl px-4 py-28 text-center">
        {{-- Decorative element --}}
        <div class="mb-8 flex items-center justify-center gap-4">
            <span class="bg-warm-500 block h-px w-16 opacity-40"></span>
            <span class="bg-warm-500 block h-2 w-2 rounded-full opacity-50"></span>
            <span class="bg-warm-500 block h-px w-16 opacity-40"></span>
        </div>

        <p class="font-script text-warm-400 mb-4 text-2xl md:text-3xl">Life's too short for store-bought</p>

        <h2 class="font-display mb-6 text-4xl leading-tight font-bold text-white md:text-6xl">{{ $heading }}</h2>

        <p class="text-warm-400 mx-auto mb-10 max-w-xl text-base leading-relaxed md:text-lg">
            {{ $subtext ?: "Every order is baked fresh just for you. We just need {$leadTimeHours} hours notice to make it perfect." }}
        </p>

        <div class="flex flex-col justify-center gap-4 sm:flex-row">
            <x-storefront.button :href="$href" size="xl"> {{ $buttonText }} </x-storefront.button>
            <x-storefront.button :href="route('storefront.menu')" variant="outline-dark" size="lg">
                See What's Baking
            </x-storefront.button>
        </div>

        {{-- Trust line --}}
        <div class="text-warm-500 mt-12 flex items-center justify-center gap-6 text-sm">
            <span class="font-script text-base">Made fresh daily</span>
            <span class="opacity-30">·</span>
            <span class="font-script text-base">Locally sourced</span>
            <span class="opacity-30">·</span>
            <span class="font-script text-base">Baked with love</span>
        </div>
    </div>
</section>
