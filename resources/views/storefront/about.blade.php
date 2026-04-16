<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/about.css') }}">

{{-- Photo-Forward Hero with Dark Overlay --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->storeName" image-class="about-hero-img" min-height="70vh" gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[70vh]">
        <x-storefront.eyebrow class="about-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'The story behind' }}</x-storefront.eyebrow>
        <h1 class="about-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
            {{ $settings->storeName }}
        </h1>
        @if ($settings->businessTagline)
        <p class="about-fade-2 font-script text-2xl md:text-3xl text-warm-400">{{ $settings->businessTagline }}</p>
        @endif
    </div>
</x-storefront.hero-section>

{{-- Stats Strip --}}
<section class="bg-warm-800">
    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            @if ($orderCount > 0)
            <div class="stat-card text-center">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ number_format($orderCount) }}+</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Orders Fulfilled</span>
            </div>
            @endif
            @if ($avgRating)
            <div class="stat-card text-center">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ number_format($avgRating, 1) }}★</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Average Rating</span>
            </div>
            @endif
            @if ($customerCount > 0)
            <div class="stat-card text-center">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ number_format($customerCount) }}+</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Happy Customers</span>
            </div>
            @endif
            <div class="stat-card text-center">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">Fresh</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Baked Daily</span>
            </div>
        </div>
    </div>
</section>

{{-- Story Section with Pull-Quote --}}
<section class="relative py-24 md:py-32 overflow-hidden bg-warm-100">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-5 gap-12 md:gap-20 items-start">
            <div class="md:col-span-3">
                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-8">{{ $content['story_eyebrow'] ?? 'Our Story' }}</x-storefront.eyebrow>

                @if ($settings->businessTagline)
                {{-- Pull-Quote style tagline --}}
                <div class="mb-12">
                    <div class="font-display font-bold leading-none mb-4" style="font-size: 5rem; color: var(--warm-500); opacity: 0.15; line-height: 0.6;">&ldquo;</div>
                    <blockquote class="font-display text-2xl md:text-3xl lg:text-4xl font-medium leading-snug text-warm-800 tracking-tight">
                        {{ $settings->businessTagline }}
                    </blockquote>
                </div>
                @endif

                <div class="space-y-5 text-lg leading-relaxed text-warm-600">
                    @if ($settings->aboutUsText)
                        @foreach (explode("\n", $settings->aboutUsText) as $paragraph)
                            @if (trim($paragraph))
                            <p>{{ trim($paragraph) }}</p>
                            @endif
                        @endforeach
                    @else
                        <p>We are passionate bakers dedicated to crafting the finest artisan breads, pastries, and treats. Every item that leaves our kitchen is made with love, premium ingredients, and time-honored techniques passed down through generations.</p>
                        <p>We believe that great baking is both an art and a science, and we pour our hearts into every loaf, every pastry, and every bite.</p>
                    @endif
                </div>
            </div>

            {{-- Photo placeholder --}}
            <div class="md:col-span-2">
                <div class="rounded-2xl overflow-hidden aspect-[4/5]">
                    <img src="{{ $settings->heroImageUrl() }}" alt="{{ $settings->storeName }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Values / What We Believe --}}
<x-storefront.dark-section padding="py-24 md:py-28" radial-position="30% 50%">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">{{ $content['values_eyebrow'] ?? 'Our Values' }}</x-storefront.eyebrow>
            <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-100">{{ $content['values_heading'] ?? 'What We Believe' }}</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach ($content['values'] ?? [
                ['title' => 'Quality Ingredients', 'description' => 'We source only the finest, freshest ingredients for every recipe. No shortcuts, no compromises — just honest baking.'],
                ['title' => 'Freshly Baked', 'description' => 'Everything is baked fresh for your order. We believe in delivering the best experience, every single time.'],
                ['title' => 'Handmade with Love', 'description' => 'Every product is handcrafted by skilled bakers who take pride in their craft and care about every detail.'],
            ] as $value)
            <div class="p-8 rounded-2xl transition-all duration-300 hover:-translate-y-1 bg-warm-800 border border-warm-700/15">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-6 bg-warm-500/15">
                    <span class="text-xl text-warm-500">✦</span>
                </div>
                <h3 class="font-display text-xl font-semibold mb-3 text-warm-200">{{ $value['title'] }}</h3>
                <p class="leading-relaxed text-warm-400">{{ $value['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>

@if ($settings->allergyDisclaimer)
<section class="bg-warm-100">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="flex items-start gap-4 p-6 rounded-2xl" style="background: var(--warm-200); border-left: 3px solid var(--warm-500);">
            <span class="text-lg flex-shrink-0">⚠️</span>
            <p class="text-sm leading-relaxed text-warm-700">
                <strong>Allergy Notice:</strong> {{ $settings->allergyDisclaimer }}
            </p>
        </div>
    </div>
</section>
@endif

{{-- Location + Social --}}
@if ($settings->storeAddress || !empty(array_filter($settings->socialMediaLinks ?? [])))
<section class="bg-warm-100">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24">
        <div class="grid md:grid-cols-2 gap-16 items-start">
            @if ($settings->storeAddress)
            <div>
                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">{{ $content['location_eyebrow'] ?? 'Find Us' }}</x-storefront.eyebrow>
                <p class="font-display text-2xl md:text-3xl leading-relaxed text-warm-800">{{ $settings->storeAddress }}</p>
            </div>
            @endif

            @if (!empty(array_filter($settings->socialMediaLinks ?? [])))
            <div>
                <x-storefront.eyebrow align="left" line-opacity="0.5" class="mb-6">{{ $content['social_eyebrow'] ?? 'Follow Along' }}</x-storefront.eyebrow>
                <x-storefront.social-links
                    :links="$settings->socialMediaLinks"
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
</x-layouts.storefront>
