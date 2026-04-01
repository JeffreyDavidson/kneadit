<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/about.css') }}">

{{-- Photo-Forward Hero with Dark Overlay --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->storeName" image-class="about-hero-img" min-height="70vh" gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[70vh]">
        <x-storefront.eyebrow class="about-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'The story behind' }}</x-storefront.eyebrow>
        <h1 class="about-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
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
                <div class="flex gap-4">
                    @if (!empty($settings->socialMediaLinks['facebook']))
                    <a href="{{ $settings->socialMediaLinks['facebook'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 bg-warm-200 text-warm-600" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    @endif
                    @if (!empty($settings->socialMediaLinks['instagram']))
                    <a href="{{ $settings->socialMediaLinks['instagram'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 bg-warm-200 text-warm-600" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    @endif
                    @if (!empty($settings->socialMediaLinks['twitter']))
                    <a href="{{ $settings->socialMediaLinks['twitter'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 bg-warm-200 text-warm-600" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    @endif
                </div>
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
