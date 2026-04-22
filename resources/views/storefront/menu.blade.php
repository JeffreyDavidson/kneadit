<x-layouts.storefront>
<link rel="stylesheet" href="{{ asset('css/menu.css') }}">

{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->store->name . ' menu'" image-class="hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="hero-fade-1 mb-6">{{ $heroEyebrow }}</x-storefront.eyebrow>
        <h1 class="hero-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-4 text-warm-100">
            {{ $content['hero_title'] ?? 'Our Menu' }}
        </h1>
        <p class="hero-fade-2 font-script text-2xl md:text-3xl text-warm-400">
            Crafted with care, baked with love
        </p>
        <p class="hero-fade-3 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed mt-4 text-warm-400">
            {{ $content['hero_subtitle'] ?? 'Everything we make, crafted with care. Browse at your pace — when something catches your eye, we\'ll have it freshly prepared just for you.' }}
        </p>
    </div>
</x-storefront.hero-section>

{{-- Category Filter Tabs --}}
@if (count($categories) > 1)
<div class="sticky top-14 md:top-16 z-30 bg-warm-900 border-b border-warm-700/15">
    <div class="max-w-7xl mx-auto px-4">
        <nav class="flex overflow-x-auto gap-2 py-4 scrollbar-hide scrollbar-none" x-data="{ active: '' }" aria-label="Menu categories">
            <a href="#all" @click="active = ''"
               class="category-tab whitespace-nowrap text-sm font-semibold px-5 py-3 rounded-full text-warm-400 bg-warm-700/15"
               :class="active === '' ? 'active' : ''"
               :aria-current="active === '' ? 'true' : 'false'">
                All
            </a>
            @foreach ($categories as $cat)
            <a href="#category-{{ $cat->id }}" @click="active = '{{ $cat->id }}'"
               class="category-tab whitespace-nowrap text-sm font-semibold px-5 py-3 rounded-full text-warm-400 bg-warm-700/15"
               :class="active === '{{ $cat->id }}' ? 'active' : ''"
               :aria-current="active === '{{ $cat->id }}' ? 'true' : 'false'">
                {{ $cat->name }}
            </a>
            @endforeach
        </nav>
    </div>
</div>
@endif

{{-- Product Grid by Category --}}
<div class="bg-warm-900">
    <div class="max-w-7xl mx-auto px-4">
        @forelse ($categories as $category)
        <section id="category-{{ $category->id }}" class="py-16 md:py-20">
            {{-- Category Header --}}
            <div class="mb-12">
                <x-storefront.eyebrow align="left" class="mb-3">{{ $content['category_eyebrow'] ?? 'Collection' }}</x-storefront.eyebrow>
                <div class="flex items-end gap-6">
                    <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-100">
                        {{ $category->name }}
                    </h2>
                    <div class="flex-1 h-px mb-3 bg-warm-700/20"></div>
                </div>
                @if ($category->description)
                <p class="mt-3 text-lg text-warm-500">{{ $category->description }}</p>
                @endif
            </div>

            {{-- Product Cards Grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($category->products as $product)
                @php($badge = \App\Presenters\ProductPresenter::for($product)->seasonalBadge())
                <x-storefront.product-card :product="$product" card-class="menu-card bg-warm-800" description-class="mb-3">
                    <x-slot:badge>
                        @if ($badge)
                        <x-storefront.pill tone="solid" size="sm" class="absolute top-4 left-4 !font-bold uppercase tracking-wider">
                            {{ $badge }}
                        </x-storefront.pill>
                        @endif
                    </x-slot:badge>
                    <x-slot:overlay>
                        <div class="menu-card-overlay absolute inset-0 flex items-center justify-center bg-warm-900/50">
                            @if ($product->is_active)
                            <x-storefront.button :href="route('order.create')" size="sm" class="menu-card-cta">
                                {{ $content['add_to_order_button'] ?? 'Add to Order' }}
                            </x-storefront.button>
                            @else
                            <span class="menu-card-cta inline-block px-6 py-3 rounded-full text-sm font-semibold bg-white/15 text-warm-300 backdrop-blur-sm">
                                Currently Unavailable
                            </span>
                            @endif
                        </div>
                    </x-slot:overlay>
                    <x-slot:footer>
                        @if (!$product->is_active)
                        <div x-data="waitlistSignup({
                            url: @js(route('productWaitlist.join')),
                            productId: {{ $product->id }},
                            csrfToken: @js(csrf_token()),
                        })">
                            <button x-show="!showForm && !submitted" @click="open()"
                                class="text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer transition-all duration-200 text-warm-400 bg-warm-700/20">
                                🔔 Notify Me When Available
                            </button>
                            <span x-show="submitted" class="text-xs font-medium text-warm-400">✓ We'll notify you!</span>
                            <form x-show="showForm" @submit.prevent="submit()" class="flex gap-1 mt-2">
                                <input x-model="email" type="email" required placeholder="Email" class="text-sm rounded-lg border border-warm-600/30 px-2 py-1 w-full bg-warm-800 text-warm-200">
                                <button type="submit" class="text-sm px-3 py-1 rounded-lg font-semibold flex-shrink-0 bg-warm-500 text-warm-900">Go</button>
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
            <p class="font-display text-2xl text-warm-400">{{ $content['empty_message'] ?? 'Our menu is being updated. Check back soon.' }}</p>
        </div>
        @endforelse
    </div>
</div>

{{-- CTA Section --}}
<section class="relative py-24 overflow-hidden bg-warm-800">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_50%,color-mix(in_srgb,var(--warm-500)_6%,transparent),transparent_60%)]" aria-hidden="true"></div>
    <div class="relative z-10 text-center max-w-2xl mx-auto px-4">
        <p class="font-script text-2xl mb-4 text-warm-500">{{ $content['cta_script'] ?? 'Ready to order?' }}</p>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 text-warm-100">
            {{ $content['cta_heading'] ?? 'Let\'s get baking.' }}
        </h2>
        <p class="text-lg mb-10 text-warm-400">

            {{ $ctaDesc }}
        </p>
        <x-storefront.button :href="route('order.create')" size="lg">
            {{ $content['cta_button'] ?? 'Place an Order' }}
        </x-storefront.button>
    </div>
</section>
</x-layouts.storefront>
