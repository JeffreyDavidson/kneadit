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
    @keyframes heroFadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .hero-fade-1 { animation: heroFadeUp 0.8s ease-out 0.2s both; }
    .hero-fade-2 { animation: heroFadeUp 0.8s ease-out 0.5s both; }
    .hero-fade-3 { animation: heroFadeUp 0.8s ease-out 0.8s both; }
</style>
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 90vh; background: var(--warm-900);">
    <!-- Animated gradient -->
    <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(212,146,12,0.12) 0%, transparent 40%, rgba(139,104,68,0.08) 60%, transparent 80%, rgba(212,146,12,0.06) 100%); background-size: 300% 300%; animation: heroGradientShift 12s ease-in-out infinite;"></div>

    <!-- Grain texture -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <!-- Decorative gold corner accents -->
    <div class="absolute top-12 left-12 w-20 h-20 hidden md:block" style="border-top: 1px solid var(--warm-500); border-left: 1px solid var(--warm-500); opacity: 0.2;"></div>
    <div class="absolute bottom-12 right-12 w-20 h-20 hidden md:block" style="border-bottom: 1px solid var(--warm-500); border-right: 1px solid var(--warm-500); opacity: 0.2;"></div>

    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
        <p class="hero-fade-1 uppercase tracking-[0.3em] text-xs md:text-sm font-medium mb-8" style="color: var(--warm-500);">Est. {{ date('Y') }}</p>
        <h1 class="hero-fade-1 font-display font-bold mb-6 tracking-tight leading-none" style="color: var(--warm-100); font-size: clamp(3.5rem, 11vw, 9rem); letter-spacing: -0.02em;">
            {{ $storeName }}
        </h1>
        <div class="hero-fade-2 flex items-center justify-center gap-4 mb-10">
            <span class="block w-12 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            <p class="font-script text-xl md:text-2xl" style="color: var(--warm-400);">
                {{ $tagline ?: 'Baked with care, shared with love' }}
            </p>
            <span class="block w-12 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
        </div>
        <div class="hero-fade-3 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.create') }}" class="inline-block px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900); font-size: 1rem;">
                Place Your Order
            </a>
            <a href="{{ route('storefront.menu') }}" class="inline-block px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: transparent; color: var(--warm-300); border: 1px solid rgba(139, 104, 68, 0.4); font-size: 1rem;">
                Browse Our Menu
            </a>
        </div>
    </div>
</section>
