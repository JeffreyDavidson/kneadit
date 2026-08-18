<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/about.css') }}" />

    @if ($storefrontTheme === 'biscotto')
        @include('storefront.themes.biscotto.about')
    @else
        {{-- Photo-Forward Hero with Dark Overlay --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            :image-alt="$settings->store->name"
            image-class="hero-img"
            min-height="70vh"
            gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)"
        >
            <div class="relative z-10 flex min-h-[70vh] flex-col items-center justify-end px-4 pb-20 text-center">
                <x-storefront.eyebrow class="hero-fade-1 mb-6">
                    {{ $content['hero_eyebrow'] ?? 'The story behind' }}</x-storefront.eyebrow>
                <h1 class="hero-fade-1 font-display text-warm-100 mb-6 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                    {{ $settings->store->name }}
                </h1>
                @if ($settings->branding->businessTagline)
                    <p class="hero-fade-2 font-script text-warm-400 text-2xl md:text-3xl">
                        {{ $settings->branding->businessTagline }}
                    </p>
                @endif
            </div>
        </x-storefront.hero-section>

        {{-- Stats Strip --}}
        <section class="bg-warm-800">
            <div class="mx-auto max-w-5xl px-4 py-12">
                <div class="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8">
                    @if ($orderCount > 0)
                        <x-storefront.stat-display :value="number_format($orderCount) . '+'" label="Orders Fulfilled" />
                    @endif
                    @if ($avgRating)
                        <x-storefront.stat-display :value="number_format($avgRating, 1) . '★'" label="Average Rating" />
                    @endif
                    @if ($customerCount > 0)
                        <x-storefront.stat-display
                            :value="number_format($customerCount) . '+'"
                            label="Happy Customers"
                        />
                    @endif
                    <x-storefront.stat-display value="Fresh" label="Baked Daily" />
                </div>
            </div>
        </section>

        {{-- Story Section with Pull-Quote --}}
        <section class="bg-warm-100 relative overflow-hidden py-24 md:py-32">
            <div class="mx-auto max-w-6xl px-4">
                <div class="grid items-start gap-12 md:grid-cols-5 md:gap-20">
                    <div class="md:col-span-3">
                        <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-8">
                            {{ $content['story_eyebrow'] ?? 'Our Story' }}</x-storefront.eyebrow>

                        @if ($settings->branding->businessTagline)
                            {{-- Pull-Quote style tagline --}}
                            <div class="mb-12">
                                <x-storefront.pull-quote-mark size="md" tone="warm" class="mb-4" />
                                <blockquote class="font-display text-warm-800 text-2xl leading-snug font-medium tracking-tight md:text-3xl lg:text-4xl">
                                    {{ $settings->branding->businessTagline }}
                                </blockquote>
                            </div>
                        @endif

                        <div class="text-warm-600 space-y-5 text-lg leading-relaxed">
                            @if ($settings->branding->aboutUsText)
                                @foreach (explode("\n", $settings->branding->aboutUsText) as $paragraph)
                                    @if (trim($paragraph))
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            @else
                                <p>
                                    We are passionate bakers dedicated to crafting the finest artisan breads, pastries,
                                    and treats. Every item that leaves our kitchen is made with love, premium
                                    ingredients, and time-honored techniques passed down through generations.
                                </p>
                                <p>
                                    We believe that great baking is both an art and a science, and we pour our hearts
                                    into every loaf, every pastry, and every bite.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Photo placeholder --}}
                    <div class="md:col-span-2">
                        <div class="aspect-[4/5] overflow-hidden rounded-2xl">
                            <img
                                src="{{ $settings->heroImageUrl() }}"
                                alt="{{ $settings->store->name }}"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Values / What We Believe --}}
        <x-storefront.dark-section padding="py-24 md:py-28" radial-position="30% 50%">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-16 text-center">
                    <x-storefront.eyebrow line-opacity="0.5" class="mb-4">
                        {{ $content['values_eyebrow'] ?? 'Our Values' }}</x-storefront.eyebrow>
                    <h2 class="font-display text-warm-100 text-3xl font-bold md:text-5xl">
                        {{ $content['values_heading'] ?? 'What We Believe' }}
                    </h2>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($content['values'] ?? [
                        ['title' => 'Quality Ingredients', 'description' => 'We source only the finest, freshest ingredients for every recipe. No shortcuts, no compromises — just honest baking.'],
                        ['title' => 'Freshly Baked', 'description' => 'Everything is baked fresh for your order. We believe in delivering the best experience, every single time.'],
                        ['title' => 'Handmade with Love', 'description' => 'Every product is handcrafted by skilled bakers who take pride in their craft and care about every detail.'],
                    ] as $value)
                        <div class="bg-warm-800 border-warm-700/15 rounded-2xl border p-8 transition-all duration-300 hover:-translate-y-1">
                            <div class="bg-warm-500/15 mb-6 flex h-12 w-12 items-center justify-center rounded-full">
                                <span class="text-warm-500 text-xl">✦</span>
                            </div>
                            <h3 class="font-display text-warm-200 mb-3 text-xl font-semibold">{{ $value['title'] }}</h3>
                            <p class="text-warm-400 leading-relaxed">{{ $value['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-storefront.dark-section>

        @if ($settings->branding->allergyDisclaimer)
            <section class="bg-warm-100">
                <div class="mx-auto max-w-4xl px-4 py-12">
                    <div class="bg-warm-200 border-warm-500 flex items-start gap-4 rounded-2xl border-l-[3px] p-6">
                        <span class="flex-shrink-0 text-lg">⚠️</span>
                        <p class="text-warm-700 text-sm leading-relaxed">
                            <strong>Allergy Notice:</strong> {{ $settings->branding->allergyDisclaimer }}
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- Location + Social --}}
        @if ($settings->store->address || ! empty(array_filter($settings->homepage->socialMediaLinks ?? [])))
            <section class="bg-warm-100">
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-24">
                    <div class="grid items-start gap-16 md:grid-cols-2">
                        @if ($settings->store->address)
                            <div>
                                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">
                                    {{ $content['location_eyebrow'] ?? 'Find Us' }}</x-storefront.eyebrow>
                                <p class="font-display text-warm-800 text-2xl leading-relaxed md:text-3xl">
                                    {{ $settings->store->address }}
                                </p>
                            </div>
                        @endif

                        @if (! empty(array_filter($settings->homepage->socialMediaLinks ?? [])))
                            <div>
                                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">
                                    {{ $content['social_eyebrow'] ?? 'Follow Along' }}</x-storefront.eyebrow>
                                <x-storefront.social-links
                                    :links="$settings->homepage->socialMediaLinks"
                                    link-class="bg-warm-200 text-warm-600 hover:bg-warm-600 hover:text-white"
                                />
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- CTA --}}
        <x-storefront.cta-section
            :script-text="$content['cta_script'] ?? 'Come taste the difference'"
            :heading="$content['cta_heading'] ?? 'Ready to place an order?'"
            :button-text="$content['cta_button'] ?? 'Order Now'"
            :button-route="route('order.create')"
        />
    @endif
</x-layouts.storefront>
