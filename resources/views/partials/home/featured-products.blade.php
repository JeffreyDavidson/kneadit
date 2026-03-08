@php
    $count = $config['count'] ?? 6;
    $title = $config['title'] ?? 'Our Favorites';
    $subtitle = $config['subtitle'] ?? 'Freshly made';
    $featuredProducts = \App\Models\Product::where('is_active', true)->take($count)->get();
@endphp
@if($featuredProducts->isNotEmpty())
<section class="py-20 px-4" style="background: var(--warm-200);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">{{ $subtitle }}</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">{{ $title }}</h2>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredProducts as $product)
            <div class="group rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl" style="background: white;">
                <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                            <span class="text-5xl font-display font-bold" style="color: var(--warm-400); opacity: 0.6;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="absolute top-4 right-4 px-4 py-1.5 rounded-full text-sm font-bold" style="background: var(--warm-900); color: var(--warm-400);">
                        ${{ number_format($product->price, 2) }}
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="font-display text-xl font-semibold mb-2" style="color: var(--warm-900);">
                        {{ $product->name }}
                    </h3>
                    @if($product->description)
                    <p class="text-sm leading-relaxed line-clamp-2" style="color: var(--warm-600);">
                        {{ $product->description }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 font-display text-lg font-semibold transition-colors hover:underline" style="color: var(--warm-700);">
                View Full Menu
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
