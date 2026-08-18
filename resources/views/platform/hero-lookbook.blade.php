<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KneadIt Hero Lookbook</title>
    @vite(["resources/css/storefront.css"])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/hero-lookbook.css') }}">
</head>
<body>

<!-- Navigation -->
<nav class="lookbook-nav">
    <h1>Hero Lookbook</h1>
    <div class="lookbook-links">
        <a href="#hero-1">1 — Photo Full</a>
        <a href="#hero-2">2 — Split Layout</a>
        <a href="#hero-3">3 — Product Grid</a>
        <a href="#hero-4">4 — Video/Motion</a>
        <a href="#hero-5">5 — Minimal Luxury</a>
        <a href="#hero-6">6 — Magazine</a>
        <a href="#hero-7">7 — Immersive Scroll</a>
        <a href="#hero-8">8 — Bold Typography</a>
    </div>
</nav>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 1 — Full Photo Background -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-1" class="relative flex items-center justify-center overflow-hidden" style="min-height: 100vh; scroll-margin-top: 60px;">
    <div class="concept-label">1 — Full Photo Background</div>

    <!-- Background image with Ken Burns -->
    <div class="absolute inset-0" style="animation: kenBurns 20s ease-in-out infinite alternate;">
        <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
             alt="" class="w-full h-full object-cover">
    </div>

    <!-- Dark gradient overlay -->
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.3) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%);"></div>

    <!-- Content -->
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto" style="padding-top: 15vh;">
        <p class="fade-up-1 uppercase tracking-[0.3em] text-sm font-medium mb-6 text-warm-400">Handcrafted with love</p>
        <h1 class="fade-up-1 font-display font-bold mb-8 leading-none" style="color: white; font-size: clamp(3rem, 10vw, 8rem); letter-spacing: -0.02em;">
            Sweet Dreams<br>Bakery
        </h1>
        <p class="fade-up-2 font-script text-2xl md:text-3xl mb-10 text-warm-300">
            Where every bite tells a story
        </p>
        <div class="fade-up-3 flex flex-col sm:flex-row gap-4 justify-center">
            <x-storefront.button href="#" size="lg">Order Now</x-storefront.button>
            <x-storefront.button href="#" variant="outline-dark" size="lg">Browse Menu</x-storefront.button>
        </div>
    </div>

    <!-- Bottom fade to next section -->
    <div class="absolute bottom-0 left-0 right-0 h-32" style="background: linear-gradient(to bottom, transparent, #111);"></div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 2 — Split Layout (Photo Right, Text Left) -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-2" class="relative overflow-hidden min-h-screen bg-warm-900 scroll-mt-[60px]">
    <div class="concept-label">2 — Split Layout</div>

    <div class="grid md:grid-cols-2 min-h-screen">
        <!-- Left: Content -->
        <div class="flex flex-col justify-center px-8 md:px-16 lg:px-24 py-20 relative">
            <!-- Decorative line -->
            <div class="absolute top-0 right-0 w-px h-full hidden md:block" style="background: linear-gradient(to bottom, transparent, var(--warm-500), transparent); opacity: 0.2;"></div>

            <div class="fade-up-1 flex items-center gap-3 mb-8">
                <span class="block w-12 h-px bg-warm-500"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">Est. 2024</span>
            </div>
            <h1 class="fade-up-1 font-display font-bold mb-6 leading-none" style="color: var(--warm-100); font-size: clamp(3rem, 6vw, 5.5rem);">
                Sweet Dreams<br>Bakery
            </h1>
            <p class="fade-up-2 text-lg md:text-xl leading-relaxed mb-8 max-w-md text-warm-400">
                Artisan baked goods crafted with locally sourced ingredients and a whole lot of love. Made fresh daily in our kitchen.
            </p>
            <div class="fade-up-3 flex flex-wrap gap-4">
                <x-storefront.button href="#" size="md">Place Your Order</x-storefront.button>
                <a href="#" class="inline-flex items-center gap-2 px-6 py-4 font-semibold transition-all duration-200 text-warm-400">
                    Our Story
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <!-- Trust badges -->
            <div class="fade-up-4 flex items-center gap-6 mt-12 pt-8 border-t border-warm-700/20">
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">500+</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">Happy Customers</span>
                </div>
                <div style="width: 1px; height: 40px; background: rgba(139,104,68,0.2);"></div>
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">4.9</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">★ Rating</span>
                </div>
                <div style="width: 1px; height: 40px; background: rgba(139,104,68,0.2);"></div>
                <div class="text-center">
                    <span class="block font-display text-2xl font-bold text-warm-400">Fresh</span>
                    <span class="text-xs uppercase tracking-wider text-warm-600">Daily</span>
                </div>
            </div>
        </div>

        <!-- Right: Image -->
        <div class="relative overflow-hidden hidden md:block">
            <img src="https://images.unsplash.com/photo-1486427944544-d2c246c4df4e?w=1200&q=80"
                 alt="" class="w-full h-full object-cover" style="animation: kenBurns 25s ease-in-out infinite alternate;">
            <!-- Overlay -->
            <div class="absolute inset-0" style="background: linear-gradient(to right, var(--warm-900) 0%, transparent 30%);"></div>
            <!-- Floating review card -->
            <div class="absolute bottom-12 left-12 right-12 p-6 rounded-2xl backdrop-blur-md" style="background: rgba(28,20,16,0.7); border: 1px solid rgba(212,146,12,0.2); animation: fadeUp 1s ease-out 1.5s both;">
                <div class="flex gap-1 mb-2">
                    <span class="text-warm-500">★★★★★</span>
                </div>
                <p class="italic text-sm text-warm-200">"The best cinnamon rolls I've ever had. Period."</p>
                <p class="text-xs mt-2 font-semibold text-warm-500">— Sarah M.</p>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 3 — Product Grid Hero -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-3" class="relative overflow-hidden" style="min-height: 100vh; background: var(--warm-100); scroll-margin-top: 60px;">
    <div class="concept-label">3 — Product Grid</div>

    <div class="max-w-7xl mx-auto px-4 py-32">
        <!-- Top bar -->
        <div class="flex items-end justify-between mb-16">
            <div>
                <p class="fade-up-1 uppercase tracking-[0.3em] text-xs font-semibold mb-4 text-warm-500">Welcome to</p>
                <h1 class="fade-up-1 font-display font-bold leading-none" style="color: var(--warm-900); font-size: clamp(3rem, 8vw, 6rem);">
                    Sweet Dreams<br>Bakery
                </h1>
            </div>
            <x-storefront.button href="#" variant="dark" size="md" class="fade-up-2 hidden md:inline-flex">
                View Full Menu →
            </x-storefront.button>
        </div>

        <!-- Product grid: 1 large + 2 stacked -->
        <div class="grid md:grid-cols-3 gap-6" style="min-height: 500px;">
            <!-- Large featured -->
            <div class="md:col-span-2 md:row-span-2 group rounded-3xl overflow-hidden relative cursor-pointer" style="animation: scaleIn 0.8s ease-out 0.3s both;">
                <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1200&q=80"
                     alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" style="min-height: 500px;">
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(28,20,16,0.8) 0%, transparent 50%);"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8 md:p-12">
                    <x-storefront.pill tone="solid" size="sm" class="mb-4 !font-bold uppercase tracking-wider">BESTSELLER</x-storefront.pill>
                    <h3 class="font-display text-3xl md:text-4xl font-bold mb-2 text-white">Signature Cinnamon Rolls</h3>
                    <p class="text-lg text-warm-300">From $4.50</p>
                </div>
            </div>

            <!-- Top right -->
            <div class="group rounded-3xl overflow-hidden relative cursor-pointer" style="animation: scaleIn 0.8s ease-out 0.5s both;">
                <img src="https://images.unsplash.com/photo-1612203985729-70726954388c?w=600&q=80"
                     alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" style="min-height: 240px;">
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(28,20,16,0.8) 0%, transparent 60%);"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="font-display text-xl font-bold text-white">Sourdough Loaves</h3>
                    <p class="text-warm-300">From $8.00</p>
                </div>
            </div>

            <!-- Bottom right -->
            <div class="group rounded-3xl overflow-hidden relative cursor-pointer" style="animation: scaleIn 0.8s ease-out 0.7s both;">
                <img src="https://images.unsplash.com/photo-1587668178277-295251f900ce?w=600&q=80"
                     alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" style="min-height: 240px;">
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(28,20,16,0.8) 0%, transparent 60%);"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="font-display text-xl font-bold text-white">Fresh Croissants</h3>
                    <p class="text-warm-300">From $3.75</p>
                </div>
            </div>
        </div>

        <!-- Tagline strip -->
        <div class="flex items-center justify-center gap-6 mt-12 py-6" style="border-top: 1px solid var(--warm-300);">
            <span class="font-script text-xl text-warm-600">Baked fresh daily</span>
            <span class="text-warm-400">·</span>
            <span class="font-script text-xl text-warm-600">Locally sourced</span>
            <span class="text-warm-400">·</span>
            <span class="font-script text-xl text-warm-600">Made with love</span>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 4 — Video/Motion Background -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-4" class="relative flex items-center justify-center overflow-hidden min-h-screen bg-warm-900 scroll-mt-[60px]">
    <div class="concept-label">4 — Video/Motion Background</div>

    <!-- Simulated video with moving gradient (real site would use <video>) -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1920&q=80"
             alt="" class="w-full h-full object-cover" style="animation: kenBurns 30s ease-in-out infinite alternate;">
    </div>
    <div class="absolute inset-0" style="background: rgba(28,20,16,0.65);"></div>

    <!-- Animated grain -->
    <x-storefront.grain-texture opacity="0.04" />

    <!-- Content: centered, cinematic -->
    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
        <!-- Animated line above -->
        <div class="flex justify-center mb-10">
            <div class="w-24 h-px" style="background: var(--warm-500); animation: slideRight 1s ease-out 0.5s both;"></div>
        </div>

        <h1 class="fade-up-1 font-display font-bold mb-4 leading-none" style="color: white; font-size: clamp(4rem, 12vw, 10rem); letter-spacing: -0.03em;">
            Sweet Dreams
        </h1>
        <p class="fade-up-2 uppercase tracking-[0.5em] text-sm md:text-base mb-12 text-warm-400">Artisan Bakery & Patisserie</p>

        <div class="fade-up-3">
            <a href="#" class="inline-block px-12 py-5 font-semibold text-lg transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900); border-radius: 4px;">
                Explore Our Menu
            </a>
        </div>

        <!-- Scroll indicator -->
        <div class="fade-up-4 mt-20">
            <div class="w-6 h-10 rounded-full mx-auto flex items-start justify-center pt-2" style="border: 2px solid rgba(232,176,74,0.3);">
                <div class="w-1 h-2 rounded-full" style="background: var(--warm-500); animation: pulse 2s infinite;"></div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 5 — Minimal Luxury -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-5" class="relative flex items-center overflow-hidden" style="min-height: 100vh; background: var(--warm-50); scroll-margin-top: 60px;">
    <div class="concept-label">5 — Minimal Luxury</div>

    <div class="max-w-7xl mx-auto px-4 w-full py-32">
        <div class="grid md:grid-cols-12 gap-8 items-center">
            <!-- Left: just the name, massive -->
            <div class="md:col-span-5">
                <div class="fade-up-1 mb-6">
                    <span class="font-script text-xl text-warm-500">Welcome to</span>
                </div>
                <h1 class="fade-up-1 font-display font-bold leading-none mb-8" style="color: var(--warm-900); font-size: clamp(3.5rem, 7vw, 6rem);">
                    Sweet<br>Dreams
                </h1>
                <div class="fade-up-2 w-16 h-1 mb-8 bg-warm-500"></div>
                <p class="fade-up-2 text-lg leading-relaxed mb-10 text-warm-600">
                    Small-batch pastries and breads, crafted daily with organic ingredients and timeless techniques.
                </p>
                <div class="fade-up-3 flex gap-4">
                    <a href="#" class="inline-block px-8 py-4 font-semibold transition-all duration-300 hover:shadow-lg" style="background: var(--warm-900); color: var(--warm-100); border-radius: 0;">
                        Order Now
                    </a>
                    <a href="#" class="inline-block px-8 py-4 font-semibold transition-all duration-300" style="color: var(--warm-700); border: 2px solid var(--warm-900); border-radius: 0;">
                        Our Menu
                    </a>
                </div>
            </div>

            <!-- Right: overlapping image composition -->
            <div class="md:col-span-7 relative" style="min-height: 600px;">
                <!-- Main image -->
                <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl" style="width: 85%; margin-left: auto; animation: scaleIn 1s ease-out 0.3s both;">
                    <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=900&q=80"
                         alt="" class="w-full h-auto object-cover" style="aspect-ratio: 3/4;">
                </div>
                <!-- Offset accent image -->
                <div class="absolute bottom-0 left-0 z-20 rounded-2xl overflow-hidden shadow-2xl" style="width: 45%; animation: scaleIn 1s ease-out 0.6s both;">
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                         alt="" class="w-full h-auto object-cover" style="aspect-ratio: 1/1;">
                </div>
                <!-- Decorative frame -->
                <div class="absolute top-8 right-8 w-full h-full rounded-2xl" style="border: 1px solid var(--warm-300); z-index: 0;"></div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 6 — Magazine/Editorial -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-6" class="relative overflow-hidden min-h-screen bg-warm-900 scroll-mt-[60px]">
    <div class="concept-label">6 — Magazine/Editorial</div>

    <!-- Background texture -->
    <x-storefront.grain-texture />

    <div class="max-w-7xl mx-auto px-4 py-32">
        <!-- Top: editorial masthead -->
        <div class="flex items-center justify-between mb-20 fade-up-1" style="border-bottom: 1px solid rgba(139,104,68,0.2); padding-bottom: 1rem;">
            <span class="uppercase tracking-[0.3em] text-xs text-warm-500">Artisan Bakery</span>
            <span class="font-script text-lg text-warm-400">Est. 2024</span>
            <span class="uppercase tracking-[0.3em] text-xs text-warm-500">Davenport, FL</span>
        </div>

        <!-- Center: huge name -->
        <div class="text-center mb-16">
            <h1 class="fade-up-1 font-display font-bold leading-none mb-8" style="color: var(--warm-100); font-size: clamp(4rem, 14vw, 12rem); letter-spacing: -0.04em;">
                Sweet Dreams
            </h1>
            <div class="fade-up-2 flex items-center justify-center gap-6">
                <span class="block w-20 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <p class="font-script text-2xl text-warm-400">Baked with passion since day one</p>
                <span class="block w-20 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
        </div>

        <!-- Bottom: 3 editorial image cards -->
        <div class="grid md:grid-cols-3 gap-6 fade-up-3">
            <div class="group cursor-pointer">
                <div class="rounded-xl overflow-hidden mb-4 aspect-[4/5]">
                    <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80"
                         alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <span class="uppercase tracking-[0.2em] text-xs text-warm-500">Fresh Daily</span>
                <h3 class="font-display text-xl mt-1 text-warm-200">Artisan Breads</h3>
            </div>
            <div class="group cursor-pointer md:-mt-12">
                <div class="rounded-xl overflow-hidden mb-4 aspect-[4/5]">
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                         alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <span class="uppercase tracking-[0.2em] text-xs text-warm-500">Signature</span>
                <h3 class="font-display text-xl mt-1 text-warm-200">Pastries & Rolls</h3>
            </div>
            <div class="group cursor-pointer">
                <div class="rounded-xl overflow-hidden mb-4 aspect-[4/5]">
                    <img src="https://images.unsplash.com/photo-1612203985729-70726954388c?w=600&q=80"
                         alt="" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <span class="uppercase tracking-[0.2em] text-xs text-warm-500">Weekend Special</span>
                <h3 class="font-display text-xl mt-1 text-warm-200">Sourdough</h3>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-16 fade-up-4">
            <x-storefront.button href="#" variant="outline-dark" size="xl">
                Explore Our Menu
            </x-storefront.button>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 7 — Immersive Scroll / Layered -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-7" class="relative flex items-center justify-center overflow-hidden" style="min-height: 100vh; scroll-margin-top: 60px;">
    <div class="concept-label">7 — Immersive Layered</div>

    <!-- Full background -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1486427944544-d2c246c4df4e?w=1920&q=80"
             alt="" class="w-full h-full object-cover">
    </div>

    <!-- Heavy bottom gradient -->
    <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(28,20,16,0.2) 0%, rgba(28,20,16,0.4) 40%, rgba(28,20,16,0.95) 85%, var(--warm-900) 100%);"></div>

    <!-- Content at bottom -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4" style="margin-top: auto; padding-bottom: 80px; padding-top: 60vh;">
        <div class="grid md:grid-cols-2 gap-12 items-end">
            <div>
                <p class="fade-up-1 font-script text-2xl mb-4 text-warm-400">Welcome to</p>
                <h1 class="fade-up-1 font-display font-bold leading-none mb-6" style="color: white; font-size: clamp(3rem, 8vw, 6rem);">
                    Sweet Dreams Bakery
                </h1>
                <div class="fade-up-2 flex gap-4">
                    <x-storefront.button href="#" size="lg">Order Now</x-storefront.button>
                    <a href="#" class="inline-flex items-center gap-2 px-6 py-4 font-semibold transition-colors text-warm-400">
                        Our Menu
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
            <div class="fade-up-3 hidden md:block">
                <!-- Featured product card floating -->
                <div class="rounded-2xl overflow-hidden shadow-2xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(212,146,12,0.2); backdrop-filter: blur(12px);">
                    <div class="flex gap-6 p-6">
                        <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=200&q=80"
                             alt="" class="w-28 h-28 rounded-xl object-cover">
                        <div class="flex flex-col justify-center">
                            <span class="text-xs uppercase tracking-wider mb-1 text-warm-500">Today's Special</span>
                            <h3 class="font-display text-xl font-bold mb-1 text-white">Cinnamon Rolls</h3>
                            <p class="text-sm text-warm-400">Warm, gooey, and fresh from the oven</p>
                            <span class="font-display text-lg font-bold mt-2 text-warm-400">$4.50</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO 8 — Bold Typography / Oversized -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section id="hero-8" class="relative overflow-hidden min-h-screen bg-warm-900 scroll-mt-[60px]">
    <div class="concept-label">8 — Bold Typography</div>

    <!-- Subtle background image, very faded -->
    <div class="absolute inset-0 opacity-10">
        <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
             alt="" class="w-full h-full object-cover">
    </div>

    <div class="relative z-10 flex flex-col justify-center min-h-screen px-4">
        <!-- Massive text filling the viewport -->
        <div class="max-w-[95vw] mx-auto text-center">
            <p class="fade-up-1 uppercase tracking-[0.5em] text-xs md:text-sm mb-8 text-warm-500">Welcome to</p>

            <!-- Name so big it almost clips -->
            <h1 class="fade-up-1 font-display font-bold leading-[0.85] mb-6" style="color: var(--warm-100); font-size: clamp(5rem, 18vw, 16rem); letter-spacing: -0.05em;">
                Sweet<br>Dreams
            </h1>

            <!-- Gold shimmer line -->
            <div class="fade-up-2 mx-auto mb-8" style="width: 200px; height: 2px; background: linear-gradient(90deg, transparent, var(--warm-500), transparent);"></div>

            <p class="fade-up-2 font-script text-2xl md:text-4xl mb-12 text-warm-400">
                Artisan bakery & patisserie
            </p>

            <div class="fade-up-3 flex flex-col sm:flex-row gap-4 justify-center">
                <x-storefront.button href="#" size="xl">Place Your Order</x-storefront.button>
                <x-storefront.button href="#" variant="outline-dark" size="xl">Browse Our Menu</x-storefront.button>
            </div>
        </div>

        <!-- Bottom strip: scrolling marquee of product names -->
        <div class="absolute bottom-0 left-0 right-0 py-4 overflow-hidden border-t border-warm-700/15">
            <div class="flex gap-12 animate-marquee whitespace-nowrap" style="animation: marquee 30s linear infinite;">
                <span class="font-display text-lg text-warm-600">Cinnamon Rolls</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Sourdough</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Croissants</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Cookies</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Banana Bread</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Brownies</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Cinnamon Rolls</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Sourdough</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Croissants</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Cookies</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Banana Bread</span>
                <span class="text-warm-700">·</span>
                <span class="font-display text-lg text-warm-600">Brownies</span>
            </div>
        </div>
    </div>

    <style @cspnonce>
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        .animate-marquee { animation: marquee 30s linear infinite; }
    </style>
</section>


<!-- Footer spacer -->
<div style="height: 100px; background: #111; display: flex; align-items: center; justify-content: center;">
    <p style="color: rgba(255,255,255,0.3); font-size: 0.9rem;">End of lookbook — scroll up or use nav to jump between concepts</p>
</div>

</body>
</html>
