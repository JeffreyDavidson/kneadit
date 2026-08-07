<style @cspnonce>
    @keyframes heroFadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes heroScaleIn {
        from {
            opacity: 0;
            transform: scale(1.05);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    @keyframes heroKenBurns {
        0% {
            transform: scale(1);
        }
        100% {
            transform: scale(1.08);
        }
    }
    @keyframes heroSlideRight {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes reviewFloat {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-6px);
        }
    }
    .hero-fade-1 {
        animation: heroFadeUp 0.9s ease-out 0.3s both;
    }
    .hero-fade-2 {
        animation: heroFadeUp 0.9s ease-out 0.6s both;
    }
    .hero-fade-3 {
        animation: heroFadeUp 0.9s ease-out 0.9s both;
    }
    .hero-fade-4 {
        animation: heroFadeUp 0.9s ease-out 1.2s both;
    }
    .hero-fade-5 {
        animation: heroFadeUp 0.9s ease-out 1.5s both;
    }
    .hero-image-zoom {
        animation: heroKenBurns 25s ease-in-out infinite alternate;
    }
    .hero-review-float {
        animation: reviewFloat 4s ease-in-out infinite;
    }
</style>

@if ($heroStyle === 'fullphoto')
    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- STYLE: Full Photo Background                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden">
        {{-- Background image with Ken Burns --}}
        <div class="absolute inset-0">
            <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}" class="hero-image-zoom h-full w-full object-cover" />
        </div>

        {{-- Dark gradient overlay (heavier now that the grain mask is gone) --}}
        <div class="from-warm-900/45 via-warm-900/65 to-warm-900/95 absolute inset-0 bg-gradient-to-b"></div>

        {{-- Content --}}
        <div class="relative z-10 mx-auto max-w-4xl px-4 pt-[15vh] text-center">
            <p class="hero-fade-1 text-warm-400 mb-6 text-sm font-medium tracking-[0.3em] uppercase">
                {{ $tagline ? 'Welcome to' : 'Handcrafted with love' }}
            </p>
            <h1 class="hero-fade-1 font-display mb-8 text-[clamp(3rem,10vw,8rem)] leading-none font-bold tracking-tight text-white">
                {{ $storeName }}
            </h1>
            <p class="hero-fade-2 font-script text-warm-300 mb-10 text-2xl md:text-3xl">
                {{ $heroTagline ?: ($tagline ?: 'Where every bite tells a story') }}
            </p>
            <div class="hero-fade-3 flex flex-col justify-center gap-4 sm:flex-row">
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
    {{-- STYLE: Split Layout (Default) — cream-forward editorial --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <section class="relative min-h-screen overflow-hidden" style="background: var(--warm-100)">
        <div class="grid min-h-screen md:grid-cols-2">
            {{-- ═══ LEFT: cream content column ═══ --}}
            <div
                class="relative flex flex-col justify-center px-8 py-24 md:px-16 lg:px-24"
                style="color: var(--warm-700)"
            >
                {{-- Vertical gold hairline at the cream/photo seam — the placeholder
                 component's asterisk-divider rhythm scaled to the macro composition.
                 Hidden on mobile where the layout stacks. --}}
                <div
                    class="pointer-events-none absolute top-0 bottom-0 hidden md:block"
                    style="right: 0; width: 1px; background: var(--warm-500); opacity: 0.4"
                    aria-hidden="true"
                ></div>

                {{-- Eyebrow: short rule + EST. year in deep tone (AAA on cream). --}}
                <div class="hero-fade-1 mb-10 flex items-center gap-3">
                    <span
                        aria-hidden="true"
                        style="display: inline-block; width: 2.5rem; height: 1px; background: var(--warm-500)"
                    ></span>
                    <span
                        class="font-body font-semibold uppercase"
                        style="font-size: 0.6875rem; letter-spacing: 0.28em; color: var(--warm-700)"
                    >Est. {{ date('Y') }}</span>
                </div>

                {{-- Optional script tagline — small italic caption above the headline.
                 Reserves the script accent for a single moment instead of using it
                 as body copy (the Pophams "one accent moment" discipline). --}}
                @if ($heroTagline)
                    <p
                        class="hero-fade-1 font-script mb-3 italic"
                        style="color: var(--warm-600); font-size: clamp(1.125rem, 1.6vw, 1.5rem); line-height: 1.2"
                    >
                        {{ $heroTagline }}
                    </p>
                @endif

                {{-- Headline: store name, display font, well-set. --}}
                <h1
                    class="hero-fade-1 font-display mb-6 tracking-tight"
                    style="
                        color: var(--warm-700);
                        font-size: clamp(2.75rem, 5.5vw, 5rem);
                        font-weight: 500;
                        line-height: 1.02;
                    "
                >
                    {{ $storeName }}
                </h1>

                {{-- Body copy: deep warm tone for AAA-on-cream legibility. Falls back
                 to a plain, slightly-editorial line that avoids the precious
                 "artisan / lovingly crafted" vocabulary the design context
                 explicitly excludes. --}}
                <p
                    class="hero-fade-2 mb-10"
                    style="
                        color: var(--warm-700);
                        font-size: clamp(1rem, 1.15vw, 1.125rem);
                        line-height: 1.62;
                        max-width: 32rem;
                    "
                >
                    {{ $aboutUs ?: ($tagline ?: 'Made by hand. Made for the table. Order what you need this week, picked up or delivered fresh.') }}
                </p>

                {{-- CTAs: solid dark pill + secondary text link.
                 The default `cta` variant is gold-on-cream which doesn't pop
                 against the cream surface — both are warm-yellow tones so the
                 button vanishes. `dark` variant (warm-900 bg + warm-100 text)
                 gives a strong ~14:1 contrast against the cream surface, which
                 is the right primary-action treatment for this hero. Gold stays
                 reserved for accents only (eyebrow rule, seam hairline). --}}
                <div class="hero-fade-3 flex flex-wrap items-center gap-x-12 gap-y-4">
                    <x-storefront.button :href="route('order.create')" variant="dark" size="md">
                        {{ $primaryCtaText }}
                    </x-storefront.button>
                    <a
                        href="{{ route('storefront.menu') }}"
                        class="inline-flex items-center gap-2 underline-offset-4 transition hover:underline"
                        style="color: var(--warm-700); font-size: 0.9375rem; font-weight: 500"
                    >
                        {{ $secondaryCtaText }}
                        <x-heroicon-o-arrow-right class="h-4 w-4" stroke-width="2" />
                    </a>
                </div>

                {{-- Credentials strip — only renders when there are 2+ real data
                 points. A solo figure ("20+ customers" alone) was floating
                 awkwardly under the CTAs, and the previous top hairline read
                 as a stray underline rather than a section divider. With 2+
                 items the strip earns its own line; below that threshold,
                 whitespace beneath the CTAs carries the composition. --}}
                @php
                    $credentialCount = (int) ($customerCount > 0) + (int) ($avgRating !== null);
                @endphp
                @if ($credentialCount >= 2)
                    <dl
                        class="hero-fade-4 mt-14 flex flex-wrap items-baseline"
                        style="color: var(--warm-700); column-gap: 2.75rem; row-gap: 0.75rem"
                    >
                        @if ($customerCount > 0)
                            <div class="flex items-baseline" style="gap: 0.5rem">
                                <dt class="sr-only">Happy customers</dt>
                                <dd class="font-display" style="font-size: 1.375rem; font-weight: 500; line-height: 1">
                                    {{ $customerCount < 10 ? $customerCount : number_format($customerCount) . '+' }}
                                </dd>
                                <span
                                    class="font-body uppercase"
                                    style="font-size: 0.6875rem; letter-spacing: 0.18em; opacity: 0.7"
                                >customers</span>
                            </div>
                        @endif

                        @if ($avgRating)
                            <div class="flex items-baseline" style="gap: 0.5rem">
                                <dt class="sr-only">Average rating</dt>
                                <dd class="font-display" style="font-size: 1.375rem; font-weight: 500; line-height: 1">
                                    {{ number_format($avgRating, 1) }}
                                </dd>
                                <span
                                    class="font-body uppercase"
                                    style="font-size: 0.6875rem; letter-spacing: 0.18em; opacity: 0.7"
                                >★ rating</span>
                            </div>
                        @endif
                    </dl>
                @endif
            </div>

            {{-- ═══ RIGHT: photo column ═══ --}}
            <div class="relative hidden overflow-hidden md:block" style="background: var(--warm-900)">
                <img
                    src="{{ $heroImageUrl }}"
                    alt="{{ $storeName }}"
                    class="hero-image-zoom h-full w-full object-cover"
                />

                {{-- Floating review card — translucent dark on photo, photo carries
                 the contrast. Reads as a notebook clipping over the image. --}}
                @if ($topReview)
                    <figure
                        class="hero-fade-5 hero-review-float absolute"
                        style="
                            bottom: clamp(1.5rem, 3vw, 3rem);
                            left: clamp(1.5rem, 3vw, 3rem);
                            right: clamp(1.5rem, 3vw, 3rem);
                            max-width: 28rem;
                            padding: 1.25rem 1.5rem;
                            background: color-mix(in oklab, var(--warm-900) 78%, transparent);
                            backdrop-filter: blur(12px);
                            -webkit-backdrop-filter: blur(12px);
                            border: 1px solid color-mix(in oklab, var(--warm-100) 18%, transparent);
                            border-radius: 4px;
                        "
                    >
                        <div class="mb-2.5 flex gap-1" style="color: var(--warm-500)">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star @class([
                                    'w-4 h-4',
                                    'opacity-100' => $i <= $topReview->rating,
                                    'opacity-25' => $i > $topReview->rating,
                                ]) />
                            @endfor
                        </div>
                        <blockquote
                            class="font-display italic"
                            style="color: var(--warm-100); font-size: 0.9375rem; line-height: 1.45"
                        >
                            &ldquo;{{ Str::limit($topReview->comment ?? '', 110) }}&rdquo;
                        </blockquote>
                        <figcaption
                            class="font-body mt-2.5 uppercase"
                            style="color: var(--warm-300); font-size: 0.6875rem; letter-spacing: 0.2em"
                        >
                            — {{ $topReview->customer_name }}
                        </figcaption>
                    </figure>
                @endif
            </div>
        </div>

        {{-- ═══ Mobile photo — shown below the content stack ═══ --}}
        <div class="relative overflow-hidden md:hidden" style="height: 320px; background: var(--warm-900)">
            <img src="{{ $heroImageUrl }}" alt="{{ $storeName }}" class="h-full w-full object-cover" />

            @if ($topReview)
                <figure
                    class="absolute"
                    style="
                        bottom: 1rem;
                        left: 1rem;
                        right: 1rem;
                        padding: 0.875rem 1.125rem;
                        background: color-mix(in oklab, var(--warm-900) 78%, transparent);
                        backdrop-filter: blur(12px);
                        -webkit-backdrop-filter: blur(12px);
                        border: 1px solid color-mix(in oklab, var(--warm-100) 18%, transparent);
                        border-radius: 4px;
                    "
                >
                    <div class="mb-1.5 flex gap-0.5" style="color: var(--warm-500)">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star @class([
                                'w-3 h-3',
                                'opacity-100' => $i <= $topReview->rating,
                                'opacity-25' => $i > $topReview->rating,
                            ]) />
                        @endfor
                    </div>
                    <blockquote
                        class="font-display italic"
                        style="color: var(--warm-100); font-size: 0.8125rem; line-height: 1.4"
                    >
                        &ldquo;{{ Str::limit($topReview->comment ?? '', 80) }}&rdquo;
                    </blockquote>
                    <figcaption
                        class="font-body mt-1.5 uppercase"
                        style="color: var(--warm-300); font-size: 0.625rem; letter-spacing: 0.2em"
                    >
                        — {{ $topReview->customer_name }}
                    </figcaption>
                </figure>
            @endif
        </div>
    </section>
@endif
