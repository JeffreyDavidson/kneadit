@php
    $heading = $config['heading'] ?? 'Treat Yourself Today';
    $subtext = $config['subtext'] ?? null;
    $buttonText = $config['button_text'] ?? 'Start Your Order';
    $buttonLink = $config['button_link'] ?? 'order';
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $heroImage = \App\Models\Setting::get('hero_image');

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
<section class="relative py-0 overflow-hidden" style="min-height: 500px;">
    {{-- Background image --}}
    <div class="absolute inset-0">
        <img src="{{ $ctaImageUrl }}" alt="" class="w-full h-full object-cover" style="filter: brightness(0.3);">
    </div>

    {{-- Gradient overlay --}}
    <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(28,20,16,0.95) 0%, rgba(28,20,16,0.7) 50%, rgba(28,20,16,0.95) 100%);"></div>

    {{-- Grain --}}
    <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    {{-- Content --}}
    <div class="relative z-10 max-w-3xl mx-auto text-center px-4 py-28">
        {{-- Decorative element --}}
        <div class="flex items-center justify-center gap-4 mb-8">
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="block w-2 h-2 rounded-full" style="background: var(--warm-500); opacity: 0.5;"></span>
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>

        <p class="font-script text-2xl md:text-3xl mb-4" style="color: var(--warm-400);">Life's too short for store-bought</p>

        <h2 class="font-display text-4xl md:text-6xl font-bold mb-6 leading-tight" style="color: white;">
            {{ $heading }}
        </h2>

        <p class="text-base md:text-lg mb-10 leading-relaxed max-w-xl mx-auto" style="color: var(--warm-400);">
            {{ $subtext ?: "Every order is baked fresh just for you. We just need {$leadTimeHours} hours notice to make it perfect." }}
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ $href }}" class="inline-block px-12 py-5 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
                {{ $buttonText }}
            </a>
            <a href="{{ route('storefront.menu') }}" class="inline-flex items-center justify-center gap-2 px-8 py-5 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105" style="border: 2px solid rgba(232,176,74,0.3); color: var(--warm-400);">
                See What's Baking
            </a>
        </div>

        {{-- Trust line --}}
        <div class="flex items-center justify-center gap-6 mt-12 text-sm" style="color: var(--warm-500);">
            <span class="font-script text-base">Made fresh daily</span>
            <span style="opacity: 0.3;">·</span>
            <span class="font-script text-base">Locally sourced</span>
            <span style="opacity: 0.3;">·</span>
            <span class="font-script text-base">Baked with love</span>
        </div>
    </div>
</section>
