<style @cspnonce>
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
<section class="relative flex items-center justify-center overflow-hidden min-h-screen">
    {{-- Background image with Ken Burns --}}
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}"
             class="w-full h-full object-cover hero-image-zoom">
    </div>

    {{-- Dark gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-b from-warm-900/30 via-warm-900/50 to-warm-900/90"></div>

    {{-- Grain texture --}}
    <x-storefront.grain-texture />

    {{-- Content --}}
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-[15vh]">
        <p class="hero-fade-1 uppercase tracking-[0.3em] text-sm font-medium mb-6 text-warm-400">
            {{ $tagline ? 'Welcome to' : 'Handcrafted with love' }}
        </p>
        <h1 class="hero-fade-1 font-display font-bold mb-8 leading-none text-white tracking-tight text-[clamp(3rem,10vw,8rem)]">
            {{ $storeName }}
        </h1>
        <p class="hero-fade-2 font-script text-2xl md:text-3xl mb-10 text-warm-300">
            {{ $heroTagline ?: ($tagline ?: 'Where every bite tells a story') }}
        </p>
        <div class="hero-fade-3 flex flex-col sm:flex-row gap-4 justify-center">
            <x-storefront.button :href="route('order.create')" size="lg">
                {{ $primaryCtaText }}
            </x-storefront.button>
            <x-storefront.button :href="route('storefront.menu')" variant="outline-dark" size="lg">
                {{ $secondaryCtaText }}
            </x-storefront.button>
        </div>
    </div>
</section>

@else
{{-- ═══════════════════════════════════════════════════════ --}}
{{-- STYLE: Split Layout (Default)                         --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden min-h-screen bg-warm-900">
    {{-- Grain texture --}}
    <x-storefront.grain-texture />

    <div class="grid md:grid-cols-2 min-h-screen">
        {{-- Left: Content --}}
        <div class="flex flex-col justify-center px-8 md:px-16 lg:px-24 py-24 relative z-10">
            {{-- Decorative vertical line on right edge --}}
            <div class="absolute top-0 right-0 w-px h-full hidden md:block opacity-15 bg-gradient-to-b from-transparent via-warm-500 to-transparent"></div>

            <div class="hero-fade-1 flex items-center gap-3 mb-8">
                <span class="block w-12 h-px bg-warm-500"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">Est. {{ date('Y') }}</span>
            </div>

            <h1 class="hero-fade-1 font-display font-bold mb-6 leading-none text-warm-100 text-[clamp(3rem,6vw,5.5rem)]">
                {{ $storeName }}
            </h1>

            <p class="hero-fade-2 text-lg md:text-xl leading-relaxed mb-8 max-w-md text-warm-400">
                {{ $aboutUs ?: ($tagline ?: 'Artisan baked goods crafted with locally sourced ingredients and a whole lot of love. Made fresh daily in our kitchen.') }}
            </p>

            <div class="hero-fade-3 flex flex-wrap gap-4">
                <x-storefront.button :href="route('order.create')" size="md">
                    {{ $primaryCtaText }}
                </x-storefront.button>
                <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 px-6 py-4 font-semibold transition-all duration-200 text-warm-400">
                    {{ $secondaryCtaText }}
                    <x-heroicon-o-arrow-right class="w-4 h-4" stroke-width="2" />
                </a>
            </div>

            {{-- Trust badges --}}
            <div class="hero-fade-4 flex items-center gap-6 mt-12 pt-8 border-t border-warm-700/20">
                @if ($customerCount > 0)
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">{{ $customerCount < 10 ? $customerCount : number_format($customerCount) . '+' }}</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">Happy Customers</span>
                </div>
                <div class="w-px h-10 bg-warm-700/20"></div>
                @endif

                @if ($avgRating)
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">★ Rating</span>
                </div>
                <div class="w-px h-10 bg-warm-700/20"></div>
                @endif

                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">Fresh</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">Daily</span>
                </div>
            </div>
        </div>

        {{-- Right: Image --}}
        <div class="relative overflow-hidden hidden md:block">
            <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}"
                 class="w-full h-full object-cover hero-image-zoom">

            {{-- Left edge gradient blend --}}
            <div class="absolute inset-0 bg-gradient-to-r from-warm-900 from-0% to-transparent to-25%"></div>

            {{-- Bottom gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t from-warm-900 from-0% to-transparent to-30%"></div>

            {{-- Floating review card --}}
            @if ($topReview)
            <div class="absolute bottom-12 left-12 right-12 p-6 rounded-2xl backdrop-blur-md hero-fade-5 hero-review-float bg-warm-900/75 border border-warm-500/20">
                <div class="flex gap-1 mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-heroicon-s-star @class([
                            'w-4 h-4',
                            'text-warm-500' => $i <= $topReview->rating,
                            'text-warm-700/30' => $i > $topReview->rating,
                        ]) />
                    @endfor
                </div>
                <p class="italic text-sm leading-relaxed text-warm-200">"{{ Str::limit($topReview->comment ?? '', 120) }}"</p>
                <p class="text-xs mt-2 font-semibold text-warm-500">— {{ $topReview->customer_name }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Mobile hero image (shown below content on small screens) --}}
    <div class="md:hidden relative overflow-hidden h-[300px] -mt-px">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-warm-900 via-transparent to-warm-900"></div>

        @if ($topReview)
        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-xl backdrop-blur-md bg-warm-900/75 border border-warm-500/20">
            <div class="flex gap-0.5 mb-1">
                @for ($i = 1; $i <= 5; $i++)
                    <x-heroicon-s-star @class([
                        'w-3 h-3',
                        'text-warm-500' => $i <= $topReview->rating,
                        'text-warm-700/30' => $i > $topReview->rating,
                    ]) />
                @endfor
            </div>
            <p class="italic text-xs text-warm-200">"{{ Str::limit($topReview->comment ?? '', 80) }}"</p>
            <p class="text-xs mt-1 font-semibold text-warm-500">— {{ $topReview->customer_name }}</p>
        </div>
        @endif
    </div>
</section>
@endif
