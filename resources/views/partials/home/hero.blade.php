@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
@endphp
<!-- Hero — full viewport, dark overlay, bold typography -->
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 85vh; background: var(--warm-900);">
    <!-- Gradient background with warmth -->
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(212, 146, 12, 0.15), transparent 60%), radial-gradient(ellipse at 70% 80%, rgba(139, 104, 68, 0.1), transparent 50%);"></div>
    
    <!-- Subtle grain texture -->
    <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <p class="font-script text-2xl md:text-3xl mb-6" style="color: var(--warm-500);">Welcome to</p>
        <h1 class="font-display text-6xl md:text-8xl font-bold mb-6 leading-tight" style="color: var(--warm-100);">
            {{ $storeName }}
        </h1>
        <div class="w-24 h-1 mx-auto mb-8" style="background: linear-gradient(to right, transparent, var(--warm-500), transparent);"></div>
        <p class="font-script text-xl md:text-2xl mb-10" style="color: var(--warm-400);">
            {{ $tagline ?: 'Where artisan dreams rise to perfection' }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.create') }}" class="inline-block text-lg px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                Place Your Order
            </a>
            <a href="{{ route('storefront.menu') }}" class="inline-block text-lg px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: transparent; color: var(--warm-200); border: 2px solid var(--warm-600);">
                Browse Our Menu
            </a>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </div>
</section>
