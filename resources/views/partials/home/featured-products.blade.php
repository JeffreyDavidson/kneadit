@php
    $count = $config['count'] ?? 6;
    $title = $config['title'] ?? 'Our Favorites';
    $subtitle = $config['subtitle'] ?? 'Freshly made with love';
    $featuredProducts = \App\Models\Product::where('is_active', true)->take($count)->get();
@endphp
@if($featuredProducts->isNotEmpty())
<style>
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

<section class="relative py-24 px-4 overflow-hidden" style="background: var(--warm-900);">
    {{-- Subtle warm glow --}}
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-16">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">From Our Ovens</span>
                </div>
                <h2 class="font-display text-4xl md:text-6xl font-bold leading-tight" style="color: var(--warm-100);">{{ $title }}</h2>
                <p class="mt-3 text-lg" style="color: var(--warm-500);">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('storefront.menu') }}" class="hidden md:inline-flex items-center gap-2 mt-6 md:mt-0 font-semibold transition-all duration-200 hover:gap-3" style="color: var(--warm-400);">
                View Full Menu
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        @if($featuredProducts->count() >= 1)
        @php $star = $featuredProducts->first(); @endphp
        {{-- Hero product: large cinematic card --}}
        <div class="product-showcase-card mb-8" style="background: var(--warm-800);">
            <div class="grid md:grid-cols-2 gap-0">
                <div class="relative overflow-hidden" style="min-height: 400px;">
                    @if($star->image)
                        <img src="{{ Storage::url($star->image) }}" alt="{{ $star->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-800));">
                            <span class="font-display font-bold" style="font-size: 8rem; color: var(--warm-600); opacity: 0.3;">{{ strtoupper(substr($star->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    {{-- Bestseller badge --}}
                    <div class="absolute top-6 left-6 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider" style="background: var(--warm-500); color: var(--warm-900);">
                        Featured
                    </div>
                </div>
                <div class="flex flex-col justify-center p-10 md:p-16">
                    <span class="font-display text-3xl md:text-4xl font-bold mb-2" style="color: var(--warm-400);">${{ number_format($star->price, 2) }}</span>
                    <h3 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: var(--warm-100);">{{ $star->name }}</h3>
                    @if($star->description)
                    <p class="text-base leading-relaxed mb-8" style="color: var(--warm-400);">{{ Str::limit($star->description, 200) }}</p>
                    @endif
                    <a href="{{ route('order.create') }}" class="inline-block self-start px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                        Order Now
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if($featuredProducts->count() > 1)
        {{-- Grid of remaining products --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredProducts->skip(1) as $product)
            <div class="product-showcase-card" style="background: var(--warm-800);">
                <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-800));">
                            <span class="text-6xl font-display font-bold" style="color: var(--warm-600); opacity: 0.3;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    {{-- Price badge --}}
                    <div class="absolute top-4 right-4 px-4 py-2 rounded-full text-sm font-bold backdrop-blur-sm" style="background: rgba(28,20,16,0.8); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.2);">
                        ${{ number_format($product->price, 2) }}
                    </div>
                    {{-- Hover overlay with CTA --}}
                    <div class="product-overlay absolute inset-0 flex items-center justify-center" style="background: rgba(28,20,16,0.5);">
                        <span class="product-cta inline-block px-6 py-3 rounded-full text-sm font-semibold" style="background: var(--warm-500); color: var(--warm-900);">
                            Add to Order
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="font-display text-xl font-semibold mb-1" style="color: var(--warm-100);">{{ $product->name }}</h3>
                    @if($product->description)
                    <p class="text-sm leading-relaxed line-clamp-2" style="color: var(--warm-500);">{{ $product->description }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Mobile CTA --}}
        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 font-semibold" style="color: var(--warm-400);">
                View Full Menu
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
