<style>
    @keyframes heroFadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes heroScaleIn {
        from { opacity: 0; transform: scale(1.05); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes heroKenBurns {
        0% { transform: scale(1); }
        100% { transform: scale(1.08); }
    }
    @keyframes heroSlideRight {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes reviewFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    .hero-fade-1 { animation: heroFadeUp 0.9s ease-out 0.3s both; }
    .hero-fade-2 { animation: heroFadeUp 0.9s ease-out 0.6s both; }
    .hero-fade-3 { animation: heroFadeUp 0.9s ease-out 0.9s both; }
    .hero-fade-4 { animation: heroFadeUp 0.9s ease-out 1.2s both; }
    .hero-fade-5 { animation: heroFadeUp 0.9s ease-out 1.5s both; }
    .hero-image-zoom { animation: heroKenBurns 25s ease-in-out infinite alternate; }
    .hero-review-float { animation: reviewFloat 4s ease-in-out infinite; }
</style>

@if ($heroStyle === 'fullphoto')
{{-- ═══════════════════════════════════════════════════════ --}}
{{-- STYLE: Full Photo Background                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 100vh;">
    {{-- Background image with Ken Burns --}}
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}"
             class="w-full h-full object-cover hero-image-zoom">
    </div>

    {{-- Dark gradient overlay --}}
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.3) 0%, rgba(28,20,16,0.5) 50%, rgba(28,20,16,0.9) 100%);"></div>

    {{-- Grain texture --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    {{-- Content --}}
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto" style="padding-top: 15vh;">
        <p class="hero-fade-1 uppercase tracking-[0.3em] text-sm font-medium mb-6" style="color: var(--warm-400);">
            {{ $tagline ? 'Welcome to' : 'Handcrafted with love' }}
        </p>
        <h1 class="hero-fade-1 font-display font-bold mb-8 leading-none" style="color: white; font-size: clamp(3rem, 10vw, 8rem); letter-spacing: -0.02em;">
            {{ $storeName }}
        </h1>
        <p class="hero-fade-2 font-script text-2xl md:text-3xl mb-10" style="color: var(--warm-300);">
            {{ $tagline ?: 'Where every bite tells a story' }}
        </p>
        <div class="hero-fade-3 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.create') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
                Order Now
            </a>
            <a href="{{ route('storefront.menu') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105" style="border: 2px solid rgba(232,176,74,0.4); color: var(--warm-300);">
                Browse Menu
            </a>
        </div>
    </div>
</section>

@else
{{-- ═══════════════════════════════════════════════════════ --}}
{{-- STYLE: Split Layout (Default)                         --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden" style="min-height: 100vh; background: var(--warm-900);">
    {{-- Grain texture --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="grid md:grid-cols-2" style="min-height: 100vh;">
        {{-- Left: Content --}}
        <div class="flex flex-col justify-center px-8 md:px-16 lg:px-24 py-24 relative z-10">
            {{-- Decorative vertical line on right edge --}}
            <div class="absolute top-0 right-0 w-px h-full hidden md:block" style="background: linear-gradient(to bottom, transparent, var(--warm-500), transparent); opacity: 0.15;"></div>

            <div class="hero-fade-1 flex items-center gap-3 mb-8">
                <span class="block w-12 h-px" style="background: var(--warm-500);"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Est. {{ date('Y') }}</span>
            </div>

            <h1 class="hero-fade-1 font-display font-bold mb-6 leading-none" style="color: var(--warm-100); font-size: clamp(3rem, 6vw, 5.5rem);">
                {{ $storeName }}
            </h1>

            <p class="hero-fade-2 text-lg md:text-xl leading-relaxed mb-8 max-w-md" style="color: var(--warm-400);">
                {{ $aboutUs ?: ($tagline ?: 'Artisan baked goods crafted with locally sourced ingredients and a whole lot of love. Made fresh daily in our kitchen.') }}
            </p>

            <div class="hero-fade-3 flex flex-wrap gap-4">
                <a href="{{ route('order.create') }}" class="inline-block px-8 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900);">
                    Place Your Order
                </a>
                <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 px-6 py-4 font-semibold transition-all duration-200" style="color: var(--warm-400);">
                    Our Menu
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- Trust badges --}}
            <div class="hero-fade-4 flex items-center gap-6 mt-12 pt-8" style="border-top: 1px solid rgba(139,104,68,0.2);">
                @if ($customerCount > 0)
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold" style="color: var(--warm-400);">{{ $customerCount < 10 ? $customerCount : number_format($customerCount) . '+' }}</span>
                    <span class="text-xs uppercase tracking-wider" style="color: var(--warm-600);">Happy Customers</span>
                </div>
                <div style="width: 1px; height: 40px; background: rgba(139,104,68,0.2);"></div>
                @endif

                @if ($avgRating)
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold" style="color: var(--warm-400);">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-xs uppercase tracking-wider" style="color: var(--warm-600);">★ Rating</span>
                </div>
                <div style="width: 1px; height: 40px; background: rgba(139,104,68,0.2);"></div>
                @endif

                <div class="text-center">
                    <span class="block font-display text-2xl font-bold" style="color: var(--warm-400);">Fresh</span>
                    <span class="text-xs uppercase tracking-wider" style="color: var(--warm-600);">Daily</span>
                </div>
            </div>
        </div>

        {{-- Right: Image --}}
        <div class="relative overflow-hidden hidden md:block">
            <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}"
                 class="w-full h-full object-cover hero-image-zoom">

            {{-- Left edge gradient blend --}}
            <div class="absolute inset-0" style="background: linear-gradient(to right, var(--warm-900) 0%, transparent 25%);"></div>

            {{-- Bottom gradient --}}
            <div class="absolute inset-0" style="background: linear-gradient(to top, var(--warm-900) 0%, transparent 30%);"></div>

            {{-- Floating review card --}}
            @if ($topReview)
            <div class="absolute bottom-12 left-12 right-12 p-6 rounded-2xl backdrop-blur-md hero-fade-5 hero-review-float" style="background: rgba(28,20,16,0.75); border: 1px solid rgba(212,146,12,0.2);">
                <div class="flex gap-1 mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4" style="color: {{ $i <= $topReview->rating ? 'var(--warm-500)' : 'rgba(139,104,68,0.3)' }};" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="italic text-sm leading-relaxed" style="color: var(--warm-200);">"{{ Str::limit($topReview->comment ?? '', 120) }}"</p>
                <p class="text-xs mt-2 font-semibold" style="color: var(--warm-500);">— {{ $topReview->customer_name }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Mobile hero image (shown below content on small screens) --}}
    <div class="md:hidden relative overflow-hidden" style="height: 300px; margin-top: -1px;">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(to bottom, var(--warm-900) 0%, transparent 30%, transparent 70%, var(--warm-900) 100%);"></div>

        @if ($topReview)
        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-xl backdrop-blur-md" style="background: rgba(28,20,16,0.75); border: 1px solid rgba(212,146,12,0.2);">
            <div class="flex gap-0.5 mb-1">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-3 h-3" style="color: {{ $i <= $topReview->rating ? 'var(--warm-500)' : 'rgba(139,104,68,0.3)' }};" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
            </div>
            <p class="italic text-xs" style="color: var(--warm-200);">"{{ Str::limit($topReview->comment ?? '', 80) }}"</p>
            <p class="text-xs mt-1 font-semibold" style="color: var(--warm-500);">— {{ $topReview->customer_name }}</p>
        </div>
        @endif
    </div>
</section>
@endif
