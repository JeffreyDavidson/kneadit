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

    <section class="bg-warm-900 relative overflow-hidden px-4 py-24">
        {{-- Subtle warm glow --}}
        <div
            class="absolute inset-0"
            style="background: radial-gradient(ellipse at 30% 50%, rgba(212, 146, 12, 0.06), transparent 60%)"
        ></div>

        <div class="relative z-10 mx-auto max-w-7xl">
            {{-- Header --}}
            <div class="mb-16 flex flex-col md:flex-row md:items-end md:justify-between">
                <div>
                    <x-storefront.eyebrow align="left" class="mb-4">From Our Ovens</x-storefront.eyebrow>
                    <h2 class="font-display text-warm-100 text-4xl leading-tight font-bold md:text-6xl">
                        {{ $title }}
                    </h2>
                    <p class="text-warm-500 mt-3 text-lg">{{ $subtitle }}</p>
                </div>
                <a
                    href="{{ route('storefront.menu') }}"
                    class="text-warm-400 mt-6 hidden items-center gap-2 font-semibold transition-all duration-200 hover:gap-3 md:mt-0 md:inline-flex"
                >
                    View Full Menu
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>

            @if ($featuredProducts->count() >= 1)
                @php $star = $featuredProducts->first(); @endphp
                {{-- Hero product: large cinematic card --}}
                <div class="product-showcase-card bg-warm-800 mb-8">
                    <div class="grid gap-0 md:grid-cols-2">
                        <div class="relative overflow-hidden" style="min-height: 400px">
                            @if ($star->image)
                                <img
                                    src="{{ Storage::url($star->image) }}"
                                    alt="{{ $star->name }}"
                                    class="h-full w-full object-cover"
                                />
                            @else
                                <x-storefront.image-placeholder
                                    :name="$star->name"
                                    :category="$star->category?->name"
                                    text-size="text-[8rem]"
                                />
                            @endif
                            {{-- Bestseller badge --}}
                            <div class="bg-warm-500 text-warm-900 absolute top-6 left-6 rounded-full px-4 py-2 text-xs font-bold tracking-wider uppercase">
                                Featured
                            </div>
                        </div>
                        <div class="flex flex-col justify-center p-10 md:p-16">
                            <span class="font-display text-warm-400 mb-2 text-3xl font-bold md:text-4xl">
                                @money($star->price)
                            </span>
                            <h3 class="font-display text-warm-100 mb-4 text-3xl font-bold md:text-4xl">
                                {{ $star->name }}
                            </h3>
                            @if ($star->description)
                                <p class="text-warm-400 mb-8 text-base leading-relaxed">
                                    {{ Str::limit($star->description, 200) }}
                                </p>
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
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProducts->skip(1) as $product)
                        <x-storefront.product-card :product="$product" card-class="product-showcase-card bg-warm-800">
                            <x-slot:overlay>
                                <div class="product-overlay bg-warm-900/50 absolute inset-0 flex items-center justify-center">
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
            <div class="mt-10 text-center md:hidden">
                <a
                    href="{{ route('storefront.menu') }}"
                    class="text-warm-400 inline-flex items-center gap-2 font-semibold"
                >
                    View Full Menu
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>
@endif
