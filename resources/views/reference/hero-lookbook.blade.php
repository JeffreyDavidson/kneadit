<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KneadIt Hero Lookbook</title>
    @vite(['resources/css/storefront.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;600;700&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/hero-lookbook.css') }}" />
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
    <section
        id="hero-1"
        class="relative flex items-center justify-center overflow-hidden"
        style="min-height: 100vh; scroll-margin-top: 60px"
    >
        <div class="concept-label">1 — Full Photo Background</div>

        <!-- Background image with Ken Burns -->
        <div class="absolute inset-0" style="animation: kenBurns 20s ease-in-out infinite alternate">
            <img
                src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
                alt=""
                class="h-full w-full object-cover"
            />
        </div>

        <!-- Dark gradient overlay -->
        <div
            class="absolute inset-0"
            style="
                background: linear-gradient(
                    to bottom,
                    rgba(28, 20, 16, 0.3) 0%,
                    rgba(28, 20, 16, 0.6) 50%,
                    rgba(28, 20, 16, 0.95) 100%
                );
            "
        ></div>

        <!-- Content -->
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center" style="padding-top: 15vh">
            <p class="fade-up-1 text-warm-400 mb-6 text-sm font-medium tracking-[0.3em] uppercase">
                Handcrafted with love
            </p>
            <h1
                class="fade-up-1 font-display mb-8 leading-none font-bold"
                style="color: white; font-size: clamp(3rem, 10vw, 8rem); letter-spacing: -0.02em"
            >
                Sweet Dreams<br />Bakery
            </h1>
            <p class="fade-up-2 font-script text-warm-300 mb-10 text-2xl md:text-3xl">Where every bite tells a story</p>
            <div class="fade-up-3 flex flex-col justify-center gap-4 sm:flex-row">
                <x-storefront.button href="#" size="lg">Order Now</x-storefront.button>
                <x-storefront.button href="#" variant="outline-dark" size="lg">Browse Menu</x-storefront.button>
            </div>
        </div>

        <!-- Bottom fade to next section -->
        <div
            class="absolute right-0 bottom-0 left-0 h-32"
            style="background: linear-gradient(to bottom, transparent, #111)"
        ></div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 2 — Split Layout (Photo Right, Text Left) -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section id="hero-2" class="bg-warm-900 relative min-h-screen scroll-mt-[60px] overflow-hidden">
        <div class="concept-label">2 — Split Layout</div>

        <div class="grid min-h-screen md:grid-cols-2">
            <!-- Left: Content -->
            <div class="relative flex flex-col justify-center px-8 py-20 md:px-16 lg:px-24">
                <!-- Decorative line -->
                <div
                    class="absolute top-0 right-0 hidden h-full w-px md:block"
                    style="
                        background: linear-gradient(to bottom, transparent, var(--warm-500), transparent);
                        opacity: 0.2;
                    "
                ></div>

                <div class="fade-up-1 mb-8 flex items-center gap-3">
                    <span class="bg-warm-500 block h-px w-12"></span>
                    <span class="text-warm-500 text-xs font-semibold tracking-[0.25em] uppercase">Est. 2024</span>
                </div>
                <h1
                    class="fade-up-1 font-display mb-6 leading-none font-bold"
                    style="color: var(--warm-100); font-size: clamp(3rem, 6vw, 5.5rem)"
                >
                    Sweet Dreams<br />Bakery
                </h1>
                <p class="fade-up-2 text-warm-400 mb-8 max-w-md text-lg leading-relaxed md:text-xl">
                    Artisan baked goods crafted with locally sourced ingredients and a whole lot of love. Made fresh
                    daily in our kitchen.
                </p>
                <div class="fade-up-3 flex flex-wrap gap-4">
                    <x-storefront.button href="#" size="md">Place Your Order</x-storefront.button>
                    <a
                        href="#"
                        class="text-warm-400 inline-flex items-center gap-2 px-6 py-4 font-semibold transition-all duration-200"
                    >
                        Our Story
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>

                <!-- Trust badges -->
                <div class="fade-up-4 border-warm-700/20 mt-12 flex items-center gap-6 border-t pt-8">
                    <div class="text-center">
                        <span class="font-display text-warm-400 block text-2xl font-bold">500+</span>
                        <span class="text-warm-600 text-xs tracking-wider uppercase">Happy Customers</span>
                    </div>
                    <div style="width: 1px; height: 40px; background: rgba(139, 104, 68, 0.2)"></div>
                    <div class="text-center">
                        <span class="font-display text-warm-400 block text-2xl font-bold">4.9</span>
                        <span class="text-warm-600 text-xs tracking-wider uppercase">★ Rating</span>
                    </div>
                    <div style="width: 1px; height: 40px; background: rgba(139, 104, 68, 0.2)"></div>
                    <div class="text-center">
                        <span class="font-display text-warm-400 block text-2xl font-bold">Fresh</span>
                        <span class="text-warm-600 text-xs tracking-wider uppercase">Daily</span>
                    </div>
                </div>
            </div>

            <!-- Right: Image -->
            <div class="relative hidden overflow-hidden md:block">
                <img
                    src="https://images.unsplash.com/photo-1486427944544-d2c246c4df4e?w=1200&q=80"
                    alt=""
                    class="h-full w-full object-cover"
                    style="animation: kenBurns 25s ease-in-out infinite alternate"
                />
                <!-- Overlay -->
                <div
                    class="absolute inset-0"
                    style="background: linear-gradient(to right, var(--warm-900) 0%, transparent 30%)"
                ></div>
                <!-- Floating review card -->
                <div
                    class="absolute right-12 bottom-12 left-12 rounded-2xl p-6 backdrop-blur-md"
                    style="
                        background: rgba(28, 20, 16, 0.7);
                        border: 1px solid rgba(212, 146, 12, 0.2);
                        animation: fadeUp 1s ease-out 1.5s both;
                    "
                >
                    <div class="mb-2 flex gap-1">
                        <span class="text-warm-500">★★★★★</span>
                    </div>
                    <p class="text-warm-200 text-sm italic">"The best cinnamon rolls I've ever had. Period."</p>
                    <p class="text-warm-500 mt-2 text-xs font-semibold">— Sarah M.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 3 — Product Grid Hero -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section
        id="hero-3"
        class="relative overflow-hidden"
        style="min-height: 100vh; background: var(--warm-100); scroll-margin-top: 60px"
    >
        <div class="concept-label">3 — Product Grid</div>

        <div class="mx-auto max-w-7xl px-4 py-32">
            <!-- Top bar -->
            <div class="mb-16 flex items-end justify-between">
                <div>
                    <p class="fade-up-1 text-warm-500 mb-4 text-xs font-semibold tracking-[0.3em] uppercase">
                        Welcome to
                    </p>
                    <h1
                        class="fade-up-1 font-display leading-none font-bold"
                        style="color: var(--warm-900); font-size: clamp(3rem, 8vw, 6rem)"
                    >
                        Sweet Dreams<br />Bakery
                    </h1>
                </div>
                <x-storefront.button href="#" variant="dark" size="md" class="fade-up-2 hidden md:inline-flex">
                    View Full Menu →
                </x-storefront.button>
            </div>

            <!-- Product grid: 1 large + 2 stacked -->
            <div class="grid gap-6 md:grid-cols-3" style="min-height: 500px">
                <!-- Large featured -->
                <div
                    class="group relative cursor-pointer overflow-hidden rounded-3xl md:col-span-2 md:row-span-2"
                    style="animation: scaleIn 0.8s ease-out 0.3s both"
                >
                    <img
                        src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1200&q=80"
                        alt=""
                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        style="min-height: 500px"
                    />
                    <div
                        class="absolute inset-0"
                        style="background: linear-gradient(to top, rgba(28, 20, 16, 0.8) 0%, transparent 50%)"
                    ></div>
                    <div class="absolute right-0 bottom-0 left-0 p-8 md:p-12">
                        <x-storefront.pill tone="solid" size="sm" class="mb-4 !font-bold tracking-wider uppercase">
                            BESTSELLER</x-storefront.pill>
                        <h3 class="font-display mb-2 text-3xl font-bold text-white md:text-4xl">
                            Signature Cinnamon Rolls
                        </h3>
                        <p class="text-warm-300 text-lg">From $4.50</p>
                    </div>
                </div>

                <!-- Top right -->
                <div
                    class="group relative cursor-pointer overflow-hidden rounded-3xl"
                    style="animation: scaleIn 0.8s ease-out 0.5s both"
                >
                    <img
                        src="https://images.unsplash.com/photo-1612203985729-70726954388c?w=600&q=80"
                        alt=""
                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        style="min-height: 240px"
                    />
                    <div
                        class="absolute inset-0"
                        style="background: linear-gradient(to top, rgba(28, 20, 16, 0.8) 0%, transparent 60%)"
                    ></div>
                    <div class="absolute right-0 bottom-0 left-0 p-6">
                        <h3 class="font-display text-xl font-bold text-white">Sourdough Loaves</h3>
                        <p class="text-warm-300">From $8.00</p>
                    </div>
                </div>

                <!-- Bottom right -->
                <div
                    class="group relative cursor-pointer overflow-hidden rounded-3xl"
                    style="animation: scaleIn 0.8s ease-out 0.7s both"
                >
                    <img
                        src="https://images.unsplash.com/photo-1587668178277-295251f900ce?w=600&q=80"
                        alt=""
                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        style="min-height: 240px"
                    />
                    <div
                        class="absolute inset-0"
                        style="background: linear-gradient(to top, rgba(28, 20, 16, 0.8) 0%, transparent 60%)"
                    ></div>
                    <div class="absolute right-0 bottom-0 left-0 p-6">
                        <h3 class="font-display text-xl font-bold text-white">Fresh Croissants</h3>
                        <p class="text-warm-300">From $3.75</p>
                    </div>
                </div>
            </div>

            <!-- Tagline strip -->
            <div
                class="mt-12 flex items-center justify-center gap-6 py-6"
                style="border-top: 1px solid var(--warm-300)"
            >
                <span class="font-script text-warm-600 text-xl">Baked fresh daily</span>
                <span class="text-warm-400">·</span>
                <span class="font-script text-warm-600 text-xl">Locally sourced</span>
                <span class="text-warm-400">·</span>
                <span class="font-script text-warm-600 text-xl">Made with love</span>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 4 — Video/Motion Background -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section
        id="hero-4"
        class="bg-warm-900 relative flex min-h-screen scroll-mt-[60px] items-center justify-center overflow-hidden"
    >
        <div class="concept-label">4 — Video/Motion Background</div>

        <!-- Simulated video with moving gradient (real site would use <video>) -->
        <div class="absolute inset-0">
            <img
                src="https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1920&q=80"
                alt=""
                class="h-full w-full object-cover"
                style="animation: kenBurns 30s ease-in-out infinite alternate"
            />
        </div>
        <div class="absolute inset-0" style="background: rgba(28, 20, 16, 0.65)"></div>

        <!-- Animated grain -->
        <x-storefront.grain-texture opacity="0.04" />

        <!-- Content: centered, cinematic -->
        <div class="relative z-10 mx-auto max-w-5xl px-4 text-center">
            <!-- Animated line above -->
            <div class="mb-10 flex justify-center">
                <div
                    class="h-px w-24"
                    style="background: var(--warm-500); animation: slideRight 1s ease-out 0.5s both"
                ></div>
            </div>

            <h1
                class="fade-up-1 font-display mb-4 leading-none font-bold"
                style="color: white; font-size: clamp(4rem, 12vw, 10rem); letter-spacing: -0.03em"
            >
                Sweet Dreams
            </h1>
            <p class="fade-up-2 text-warm-400 mb-12 text-sm tracking-[0.5em] uppercase md:text-base">
                Artisan Bakery & Patisserie
            </p>

            <div class="fade-up-3">
                <a
                    href="#"
                    class="inline-block px-12 py-5 text-lg font-semibold transition-all duration-300 hover:scale-105"
                    style="background: var(--warm-500); color: var(--warm-900); border-radius: 4px"
                >
                    Explore Our Menu
                </a>
            </div>

            <!-- Scroll indicator -->
            <div class="fade-up-4 mt-20">
                <div
                    class="mx-auto flex h-10 w-6 items-start justify-center rounded-full pt-2"
                    style="border: 2px solid rgba(232, 176, 74, 0.3)"
                >
                    <div
                        class="h-2 w-1 rounded-full"
                        style="background: var(--warm-500); animation: pulse 2s infinite"
                    ></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 5 — Minimal Luxury -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section
        id="hero-5"
        class="relative flex items-center overflow-hidden"
        style="min-height: 100vh; background: var(--warm-50); scroll-margin-top: 60px"
    >
        <div class="concept-label">5 — Minimal Luxury</div>

        <div class="mx-auto w-full max-w-7xl px-4 py-32">
            <div class="grid items-center gap-8 md:grid-cols-12">
                <!-- Left: just the name, massive -->
                <div class="md:col-span-5">
                    <div class="fade-up-1 mb-6">
                        <span class="font-script text-warm-500 text-xl">Welcome to</span>
                    </div>
                    <h1
                        class="fade-up-1 font-display mb-8 leading-none font-bold"
                        style="color: var(--warm-900); font-size: clamp(3.5rem, 7vw, 6rem)"
                    >
                        Sweet<br />Dreams
                    </h1>
                    <div class="fade-up-2 bg-warm-500 mb-8 h-1 w-16"></div>
                    <p class="fade-up-2 text-warm-600 mb-10 text-lg leading-relaxed">
                        Small-batch pastries and breads, crafted daily with organic ingredients and timeless techniques.
                    </p>
                    <div class="fade-up-3 flex gap-4">
                        <a
                            href="#"
                            class="inline-block px-8 py-4 font-semibold transition-all duration-300 hover:shadow-lg"
                            style="background: var(--warm-900); color: var(--warm-100); border-radius: 0"
                        >
                            Order Now
                        </a>
                        <a
                            href="#"
                            class="inline-block px-8 py-4 font-semibold transition-all duration-300"
                            style="color: var(--warm-700); border: 2px solid var(--warm-900); border-radius: 0"
                        >
                            Our Menu
                        </a>
                    </div>
                </div>

                <!-- Right: overlapping image composition -->
                <div class="relative md:col-span-7" style="min-height: 600px">
                    <!-- Main image -->
                    <div
                        class="relative z-10 overflow-hidden rounded-2xl shadow-2xl"
                        style="width: 85%; margin-left: auto; animation: scaleIn 1s ease-out 0.3s both"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=900&q=80"
                            alt=""
                            class="h-auto w-full object-cover"
                            style="aspect-ratio: 3/4"
                        />
                    </div>
                    <!-- Offset accent image -->
                    <div
                        class="absolute bottom-0 left-0 z-20 overflow-hidden rounded-2xl shadow-2xl"
                        style="width: 45%; animation: scaleIn 1s ease-out 0.6s both"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                            alt=""
                            class="h-auto w-full object-cover"
                            style="aspect-ratio: 1/1"
                        />
                    </div>
                    <!-- Decorative frame -->
                    <div
                        class="absolute top-8 right-8 h-full w-full rounded-2xl"
                        style="border: 1px solid var(--warm-300); z-index: 0"
                    ></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 6 — Magazine/Editorial -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section id="hero-6" class="bg-warm-900 relative min-h-screen scroll-mt-[60px] overflow-hidden">
        <div class="concept-label">6 — Magazine/Editorial</div>

        <!-- Background texture -->
        <x-storefront.grain-texture />

        <div class="mx-auto max-w-7xl px-4 py-32">
            <!-- Top: editorial masthead -->
            <div
                class="fade-up-1 mb-20 flex items-center justify-between"
                style="border-bottom: 1px solid rgba(139, 104, 68, 0.2); padding-bottom: 1rem"
            >
                <span class="text-warm-500 text-xs tracking-[0.3em] uppercase">Artisan Bakery</span>
                <span class="font-script text-warm-400 text-lg">Est. 2024</span>
                <span class="text-warm-500 text-xs tracking-[0.3em] uppercase">Davenport, FL</span>
            </div>

            <!-- Center: huge name -->
            <div class="mb-16 text-center">
                <h1
                    class="fade-up-1 font-display mb-8 leading-none font-bold"
                    style="color: var(--warm-100); font-size: clamp(4rem, 14vw, 12rem); letter-spacing: -0.04em"
                >
                    Sweet Dreams
                </h1>
                <div class="fade-up-2 flex items-center justify-center gap-6">
                    <span class="block h-px w-20" style="background: var(--warm-500); opacity: 0.5"></span>
                    <p class="font-script text-warm-400 text-2xl">Baked with passion since day one</p>
                    <span class="block h-px w-20" style="background: var(--warm-500); opacity: 0.5"></span>
                </div>
            </div>

            <!-- Bottom: 3 editorial image cards -->
            <div class="fade-up-3 grid gap-6 md:grid-cols-3">
                <div class="group cursor-pointer">
                    <div class="mb-4 aspect-[4/5] overflow-hidden rounded-xl">
                        <img
                            src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80"
                            alt=""
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>
                    <span class="text-warm-500 text-xs tracking-[0.2em] uppercase">Fresh Daily</span>
                    <h3 class="font-display text-warm-200 mt-1 text-xl">Artisan Breads</h3>
                </div>
                <div class="group cursor-pointer md:-mt-12">
                    <div class="mb-4 aspect-[4/5] overflow-hidden rounded-xl">
                        <img
                            src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                            alt=""
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>
                    <span class="text-warm-500 text-xs tracking-[0.2em] uppercase">Signature</span>
                    <h3 class="font-display text-warm-200 mt-1 text-xl">Pastries & Rolls</h3>
                </div>
                <div class="group cursor-pointer">
                    <div class="mb-4 aspect-[4/5] overflow-hidden rounded-xl">
                        <img
                            src="https://images.unsplash.com/photo-1612203985729-70726954388c?w=600&q=80"
                            alt=""
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>
                    <span class="text-warm-500 text-xs tracking-[0.2em] uppercase">Weekend Special</span>
                    <h3 class="font-display text-warm-200 mt-1 text-xl">Sourdough</h3>
                </div>
            </div>

            <!-- CTA -->
            <div class="fade-up-4 mt-16 text-center">
                <x-storefront.button href="#" variant="outline-dark" size="xl"> Explore Our Menu </x-storefront.button>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- HERO 7 — Immersive Scroll / Layered -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <section
        id="hero-7"
        class="relative flex items-center justify-center overflow-hidden"
        style="min-height: 100vh; scroll-margin-top: 60px"
    >
        <div class="concept-label">7 — Immersive Layered</div>

        <!-- Full background -->
        <div class="absolute inset-0">
            <img
                src="https://images.unsplash.com/photo-1486427944544-d2c246c4df4e?w=1920&q=80"
                alt=""
                class="h-full w-full object-cover"
            />
        </div>

        <!-- Heavy bottom gradient -->
        <div
            class="absolute inset-0"
            style="
                background: linear-gradient(
                    180deg,
                    rgba(28, 20, 16, 0.2) 0%,
                    rgba(28, 20, 16, 0.4) 40%,
                    rgba(28, 20, 16, 0.95) 85%,
                    var(--warm-900) 100%
                );
            "
        ></div>

        <!-- Content at bottom -->
        <div
            class="relative z-10 mx-auto w-full max-w-7xl px-4"
            style="margin-top: auto; padding-bottom: 80px; padding-top: 60vh"
        >
            <div class="grid items-end gap-12 md:grid-cols-2">
                <div>
                    <p class="fade-up-1 font-script text-warm-400 mb-4 text-2xl">Welcome to</p>
                    <h1
                        class="fade-up-1 font-display mb-6 leading-none font-bold"
                        style="color: white; font-size: clamp(3rem, 8vw, 6rem)"
                    >
                        Sweet Dreams Bakery
                    </h1>
                    <div class="fade-up-2 flex gap-4">
                        <x-storefront.button href="#" size="lg">Order Now</x-storefront.button>
                        <a
                            href="#"
                            class="text-warm-400 inline-flex items-center gap-2 px-6 py-4 font-semibold transition-colors"
                        >
                            Our Menu
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </a>
                    </div>
                </div>
                <div class="fade-up-3 hidden md:block">
                    <!-- Featured product card floating -->
                    <div
                        class="overflow-hidden rounded-2xl shadow-2xl"
                        style="
                            background: rgba(255, 255, 255, 0.05);
                            border: 1px solid rgba(212, 146, 12, 0.2);
                            backdrop-filter: blur(12px);
                        "
                    >
                        <div class="flex gap-6 p-6">
                            <img
                                src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=200&q=80"
                                alt=""
                                class="h-28 w-28 rounded-xl object-cover"
                            />
                            <div class="flex flex-col justify-center">
                                <span class="text-warm-500 mb-1 text-xs tracking-wider uppercase">Today's Special</span>
                                <h3 class="font-display mb-1 text-xl font-bold text-white">Cinnamon Rolls</h3>
                                <p class="text-warm-400 text-sm">Warm, gooey, and fresh from the oven</p>
                                <span class="font-display text-warm-400 mt-2 text-lg font-bold">$4.50</span>
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
    <section id="hero-8" class="bg-warm-900 relative min-h-screen scroll-mt-[60px] overflow-hidden">
        <div class="concept-label">8 — Bold Typography</div>

        <!-- Subtle background image, very faded -->
        <div class="absolute inset-0 opacity-10">
            <img
                src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
                alt=""
                class="h-full w-full object-cover"
            />
        </div>

        <div class="relative z-10 flex min-h-screen flex-col justify-center px-4">
            <!-- Massive text filling the viewport -->
            <div class="mx-auto max-w-[95vw] text-center">
                <p class="fade-up-1 text-warm-500 mb-8 text-xs tracking-[0.5em] uppercase md:text-sm">Welcome to</p>

                <!-- Name so big it almost clips -->
                <h1
                    class="fade-up-1 font-display mb-6 leading-[0.85] font-bold"
                    style="color: var(--warm-100); font-size: clamp(5rem, 18vw, 16rem); letter-spacing: -0.05em"
                >
                    Sweet<br />Dreams
                </h1>

                <!-- Gold shimmer line -->
                <div
                    class="fade-up-2 mx-auto mb-8"
                    style="
                        width: 200px;
                        height: 2px;
                        background: linear-gradient(90deg, transparent, var(--warm-500), transparent);
                    "
                ></div>

                <p class="fade-up-2 font-script text-warm-400 mb-12 text-2xl md:text-4xl">
                    Artisan bakery & patisserie
                </p>

                <div class="fade-up-3 flex flex-col justify-center gap-4 sm:flex-row">
                    <x-storefront.button href="#" size="xl">Place Your Order</x-storefront.button>
                    <x-storefront.button href="#" variant="outline-dark" size="xl">Browse Our Menu</x-storefront.button>
                </div>
            </div>

            <!-- Bottom strip: scrolling marquee of product names -->
            <div class="border-warm-700/15 absolute right-0 bottom-0 left-0 overflow-hidden border-t py-4">
                <div
                    class="animate-marquee flex gap-12 whitespace-nowrap"
                    style="animation: marquee 30s linear infinite"
                >
                    <span class="font-display text-warm-600 text-lg">Cinnamon Rolls</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Sourdough</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Croissants</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Cookies</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Banana Bread</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Brownies</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Cinnamon Rolls</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Sourdough</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Croissants</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Cookies</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Banana Bread</span>
                    <span class="text-warm-700">·</span>
                    <span class="font-display text-warm-600 text-lg">Brownies</span>
                </div>
            </div>
        </div>

        <style @cspnonce>
            @keyframes marquee {
                from {
                    transform: translateX(0);
                }
                to {
                    transform: translateX(-50%);
                }
            }
            .animate-marquee {
                animation: marquee 30s linear infinite;
            }
        </style>
    </section>

    <!-- Footer spacer -->
    <div style="height: 100px; background: #111; display: flex; align-items: center; justify-content: center">
        <p style="color: rgba(255, 255, 255, 0.3); font-size: 0.9rem">
            End of lookbook — scroll up or use nav to jump between concepts
        </p>
    </div>
</body>
</html>
