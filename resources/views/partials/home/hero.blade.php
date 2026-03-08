@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
@endphp
<style>
    @keyframes heroGradientShift {
        0%   { background-position: 0% 50%; }
        50%  { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 100vh; background: var(--warm-900);">
    <!-- Animated gradient -->
    <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(212,146,12,0.12) 0%, transparent 40%, rgba(139,104,68,0.08) 60%, transparent 80%, rgba(212,146,12,0.06) 100%); background-size: 300% 300%; animation: heroGradientShift 12s ease-in-out infinite;"></div>

    <!-- Grain texture -->
    <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
        <h1 class="font-display font-bold mb-8 tracking-tight" style="color: var(--warm-100); font-size: clamp(4rem, 12vw, 10rem); letter-spacing: -0.02em;">
            {{ $storeName }}
        </h1>
        <p class="font-script text-2xl md:text-3xl mb-12" style="color: var(--warm-400);">
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
</section>
