@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
    $heroImage = \App\Models\Setting::get('hero_image');
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
    @keyframes heroScaleIn {
        from { opacity: 0; transform: scale(1.1); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes scrollPulse {
        0%, 100% { opacity: 0.4; transform: translateY(0); }
        50% { opacity: 1; transform: translateY(6px); }
    }
    .hero-fade-1 { animation: heroFadeUp 0.9s ease-out 0.3s both; }
    .hero-fade-2 { animation: heroFadeUp 0.9s ease-out 0.6s both; }
    .hero-fade-3 { animation: heroFadeUp 0.9s ease-out 0.9s both; }
    .hero-fade-4 { animation: heroFadeUp 0.9s ease-out 1.2s both; }
    .hero-bg-image { animation: heroScaleIn 1.5s ease-out both; }
</style>
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 100vh; background: var(--warm-900);">
    {{-- Background image or animated gradient --}}
    @if($heroImage)
        <div class="absolute inset-0 hero-bg-image">
            <img src="{{ Storage::url($heroImage) }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
            <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.3) 40%, rgba(0,0,0,0.6) 100%);"></div>
        </div>
    @else
        <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(212,146,12,0.12) 0%, transparent 40%, rgba(139,104,68,0.08) 60%, transparent 80%, rgba(212,146,12,0.06) 100%); background-size: 300% 300%; animation: heroGradientShift 12s ease-in-out infinite;"></div>
    @endif

    {{-- Grain texture --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    {{-- Decorative gold corner accents --}}
    <div class="absolute top-10 left-10 w-24 h-24 hidden lg:block" style="border-top: 1px solid var(--warm-500); border-left: 1px solid var(--warm-500); opacity: 0.15;"></div>
    <div class="absolute top-10 right-10 w-24 h-24 hidden lg:block" style="border-top: 1px solid var(--warm-500); border-right: 1px solid var(--warm-500); opacity: 0.15;"></div>
    <div class="absolute bottom-10 left-10 w-24 h-24 hidden lg:block" style="border-bottom: 1px solid var(--warm-500); border-left: 1px solid var(--warm-500); opacity: 0.15;"></div>
    <div class="absolute bottom-10 right-10 w-24 h-24 hidden lg:block" style="border-bottom: 1px solid var(--warm-500); border-right: 1px solid var(--warm-500); opacity: 0.15;"></div>

    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
        <p class="hero-fade-1 uppercase tracking-[0.3em] text-xs md:text-sm font-body font-semibold mb-8" style="color: var(--warm-500);">Est. {{ date('Y') }}</p>

        <h1 class="hero-fade-1 font-display font-bold mb-8 tracking-tight leading-none" style="color: var(--warm-50); font-size: clamp(3rem, 10vw, 8.5rem); letter-spacing: -0.02em;">
            {{ $storeName }}
        </h1>

        <div class="hero-fade-2 flex items-center justify-center gap-4 mb-6">
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="block w-1.5 h-1.5 rounded-full" style="background: var(--warm-500); opacity: 0.6;"></span>
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>

        <p class="hero-fade-2 font-script text-xl md:text-3xl mb-12" style="color: var(--warm-300);">
            {{ $tagline ?: 'Baked with care, shared with love' }}
        </p>

        <div class="hero-fade-3 flex flex-col sm:flex-row gap-4 justify-center">
            <x-storefront.button href="{{ route('order.create') }}" variant="primary" size="lg">
                Place Your Order
            </x-storefront.button>
            <x-storefront.button href="{{ route('storefront.menu') }}" variant="secondary" size="lg" class="!border-white/20" style="color: var(--warm-300); border-color: rgba(139,104,68,0.4);">
                Browse Our Menu
            </x-storefront.button>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="hero-fade-4 absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2" style="animation: scrollPulse 2s ease-in-out infinite;">
        <span class="text-[10px] uppercase tracking-[0.2em] font-body" style="color: var(--warm-500);">Scroll</span>
        <svg class="w-4 h-4" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7"/>
        </svg>
    </div>
</section>
