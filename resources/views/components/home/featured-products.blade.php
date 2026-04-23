@if ($featuredProducts->isNotEmpty())
<style @cspnonce>
    .product-showcase-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .product-showcase-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 60px rgba(28, 20, 16, 0.4);
    }
    .product-showcase-card:hover img {
        transform: scale(1.08);
    }
    .product-showcase-card img {
        transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .product-showcase-card:hover .product-overlay {
        opacity: 1;
    }
    .product-showcase-card:hover .product-cta {
        transform: translateY(0);
        opacity: 1;
    }
    .product-overlay {
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .product-cta {
        transform: translateY(10px);
        opacity: 0;
        transition: all 0.4s ease 0.1s;
    }
</style>

<section class="relative py-24 px-4 overflow-hidden bg-warm-900">
    {{-- Subtle warm glow --}}
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-16">
            <div>
                <x-storefront.eyebrow align="left" class="mb-4">From Our Ovens</x-storefront.eyebrow>
                <h2 class="font-display text-4xl md:text-6xl font-bold leading-tight text-warm-100">{{ $title }}</h2>
                <p class="mt-3 text-lg text-warm-500">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('storefront.menu') }}" class="hidden md:inline-flex items-center gap-2 mt-6 md:mt-0 font-semibold transition-all duration-200 hover:gap-3 text-warm-400">
                View Full Menu
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        @if ($featuredProducts->count() >= 1)
        @php $star = $featuredProducts->first(); @endphp
        {{-- Hero product: large cinematic card --}}
        <div class="product-showcase-card mb-8 bg-warm-800">
            <div class="grid md:grid-cols-2 gap-0">
                <div class="relative overflow-hidden" style="min-height: 400px;">
                    @if ($star->image)
                        <img src="{{ Storage::url($star->image) }}" alt="{{ $star->name }}" class="w-full h-full object-cover">
                    @else
                        <x-storefront.image-placeholder :name="$star->name" text-size="text-[8rem]" />
                    @endif
                    {{-- Bestseller badge --}}
                    <div class="absolute top-6 left-6 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-warm-500 text-warm-900">
                        Featured
                    </div>
                </div>
                <div class="flex flex-col justify-center p-10 md:p-16">
                    <span class="font-display text-3xl md:text-4xl font-bold mb-2 text-warm-400">@money($star->price)</span>
                    <h3 class="font-display text-3xl md:text-4xl font-bold mb-4 text-warm-100">{{ $star->name }}</h3>
                    @if ($star->description)
                    <p class="text-base leading-relaxed mb-8 text-warm-400">{{ Str::limit($star->description, 200) }}</p>
                    @endif
                    <x-storefront.button :href="route('order.create')" size="md" class="self-start">
                        Order Now
                    </x-storefront.button>
                </div>
            </div>
        </div>
        @endif

        @if ($featuredProducts->count() > 1)
        {{-- Grid of remaining products --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($featuredProducts->skip(1) as $product)
            <x-storefront.product-card :product="$product" card-class="product-showcase-card bg-warm-800">
                <x-slot:overlay>
                    <div class="product-overlay absolute inset-0 flex items-center justify-center bg-warm-900/50">
                        <x-storefront.pill tone="solid" size="sm" class="product-cta">
                            Add to Order
                        </x-storefront.pill>
                    </div>
                </x-slot:overlay>
            </x-storefront.product-card>
            @endforeach
        </div>
        @endif

        {{-- Mobile CTA --}}
        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 font-semibold text-warm-400">
                View Full Menu
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
