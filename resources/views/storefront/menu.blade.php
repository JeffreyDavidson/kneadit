<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}" />

    {{-- Photo-Forward Hero --}}
    <x-storefront.hero-section
        :image="$settings->heroImageUrl()"
        :image-alt="$settings->store->name . ' menu'"
        image-class="hero-img"
    >
        <div class="relative z-10 flex min-h-[55vh] flex-col items-center justify-end px-4 pb-20 text-center">
            <x-storefront.eyebrow class="hero-fade-1 mb-6">{{ $heroEyebrow }}</x-storefront.eyebrow>
            <h1 class="hero-fade-1 font-display text-warm-100 mb-4 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                {{ $content['hero_title'] ?? 'Our Menu' }}
            </h1>
            <p class="hero-fade-2 font-script text-warm-300 text-2xl md:text-3xl">Crafted with care, baked with love</p>
            <p class="hero-fade-3 text-warm-100 mx-auto mt-4 max-w-2xl text-lg leading-relaxed md:text-xl">
                {{ $content['hero_subtitle'] ?? 'Everything we make, crafted with care. Browse at your pace — when something catches your eye, we\'ll have it freshly prepared just for you.' }}
            </p>
        </div>
    </x-storefront.hero-section>

    {{-- Category Filter Tabs --}}
    @if (count($categories) > 1)
        <div class="bg-warm-900 border-warm-700/15 sticky top-14 z-30 border-b md:top-16">
            <div class="mx-auto max-w-7xl px-4">
                <nav
                    class="scrollbar-hide scrollbar-none flex gap-2 overflow-x-auto py-4"
                    x-data="{ active: '' }"
                    aria-label="Menu categories"
                >
                    <a
                        href="#all"
                        @click="active = ''"
                        class="category-tab text-warm-400 bg-warm-700/15 rounded-full px-5 py-3 text-sm font-semibold whitespace-nowrap"
                        :class="active === '' ? 'active' : ''"
                        :aria-current="active === '' ? 'true' : 'false'"
                    >
                        All
                    </a>
                    @foreach ($categories as $cat)
                        <a
                            href="#category-{{ $cat->id }}"
                            @click="active = '{{ $cat->id }}'"
                            class="category-tab text-warm-400 bg-warm-700/15 rounded-full px-5 py-3 text-sm font-semibold whitespace-nowrap"
                            :class="active === '{{ $cat->id }}' ? 'active' : ''"
                            :aria-current="active === '{{ $cat->id }}' ? 'true' : 'false'"
                        >
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    {{-- Product Grid by Category --}}
    <div class="bg-warm-900">
        <div class="mx-auto max-w-7xl px-4">
            @forelse ($categories as $category)
                <section id="category-{{ $category->id }}" class="py-16 md:py-20">
                    {{-- Category Header --}}
                    <div class="mb-12">
                        <x-storefront.eyebrow align="left" class="mb-3">
                            {{ $content['category_eyebrow'] ?? 'Collection' }}</x-storefront.eyebrow>
                        <div class="flex items-end gap-6">
                            <h2 class="font-display text-warm-100 text-3xl font-bold md:text-5xl">
                                {{ $category->name }}
                            </h2>
                            <div class="bg-warm-700/20 mb-3 h-px flex-1"></div>
                        </div>
                        @if ($category->description)
                            <p class="text-warm-500 mt-3 text-lg">{{ $category->description }}</p>
                        @endif
                    </div>

                    {{-- Product Cards Grid --}}
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->products as $product)
                            @php($badge = \App\Presenters\ProductPresenter::for($product)->seasonalBadge())
                            <x-storefront.product-card
                                :product="$product"
                                card-class="menu-card bg-warm-800"
                                description-class="mb-3"
                            >
                                <x-slot:badge>
                                    @if ($badge)
                                        <x-storefront.pill
                                            tone="solid"
                                            size="sm"
                                            class="absolute top-4 left-4 !font-bold tracking-wider uppercase"
                                        >
                                            {{ $badge }}
                                        </x-storefront.pill>
                                    @endif
                                </x-slot:badge>
                                <x-slot:overlay>
                                    <div class="menu-card-overlay bg-warm-900/50 absolute inset-0 flex items-center justify-center">
                                        @if ($product->is_active)
                                            <x-storefront.button
                                                :href="route('order.create')"
                                                size="sm"
                                                class="menu-card-cta"
                                            >
                                                {{ $content['add_to_order_button'] ?? 'Add to Order' }}
                                            </x-storefront.button>
                                        @else
                                            <span class="menu-card-cta text-warm-300 inline-block rounded-full bg-white/15 px-6 py-3 text-sm font-semibold backdrop-blur-sm">
                                                Currently Unavailable
                                            </span>
                                        @endif
                                    </div>
                                </x-slot:overlay>
                                <x-slot:footer>
                                    @if (! $product->is_active)
                                        <div
                                            x-data="waitlistSignup({
                            url: @js(route('productWaitlist.join')),
                            productId: {{ $product->id }},
                            csrfToken: @js(csrf_token()),
                        })"
                                        >
                                            <button
                                                x-show="! showForm && ! submitted"
                                                @click="open()"
                                                class="text-warm-400 bg-warm-700/20 cursor-pointer rounded-full px-3 py-1.5 text-xs font-medium transition-all duration-200"
                                            >
                                                🔔 Notify Me When Available
                                            </button>
                                            <span x-show="submitted" class="text-warm-400 text-xs font-medium"
                                                >✓ We'll notify you!</span>
                                            <form x-show="showForm" @submit.prevent="submit()" class="mt-2 flex gap-1">
                                                <input
                                                    x-model="email"
                                                    type="email"
                                                    required
                                                    placeholder="Email"
                                                    class="border-warm-600/30 bg-warm-800 text-warm-200 w-full rounded-lg border px-2 py-1 text-sm"
                                                />
                                                <button
                                                    type="submit"
                                                    class="bg-warm-500 text-warm-900 flex-shrink-0 rounded-lg px-3 py-1 text-sm font-semibold"
                                                >
                                                    Go
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </x-slot:footer>
                            </x-storefront.product-card>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="py-24 text-center">
                    <p class="font-display text-warm-400 text-2xl">
                        {{ $content['empty_message'] ?? 'Our menu is being updated. Check back soon.' }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- CTA Section --}}
    <section class="bg-warm-800 relative overflow-hidden py-24">
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_50%,color-mix(in_srgb,var(--warm-500)_6%,transparent),transparent_60%)]"
            aria-hidden="true"
        ></div>
        <div class="relative z-10 mx-auto max-w-2xl px-4 text-center">
            <p class="font-script text-warm-500 mb-4 text-2xl">{{ $content['cta_script'] ?? 'Ready to order?' }}</p>
            <h2 class="font-display text-warm-100 mb-6 text-3xl font-bold md:text-5xl">
                {{ $content['cta_heading'] ?? 'Let\'s get baking.' }}
            </h2>
            <p class="text-warm-400 mb-10 text-lg">{{ $ctaDesc }}</p>
            <x-storefront.button :href="route('order.create')" size="lg">
                {{ $content['cta_button'] ?? 'Place an Order' }}
            </x-storefront.button>
        </div>
    </section>
</x-layouts.storefront>
