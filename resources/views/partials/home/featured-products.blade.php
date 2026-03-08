@php
    $count = $config['count'] ?? 6;
    $title = $config['title'] ?? 'Our Favorites';
    $subtitle = $config['subtitle'] ?? 'Freshly made';
    $featuredProducts = \App\Models\Product::where('is_active', true)->take($count)->get();
@endphp
@if($featuredProducts->isNotEmpty())
<x-storefront.divider style="ornament" width="md" />

<x-storefront.section bg="cream" padding="xl">
    <x-storefront.section-header
        eyebrow="From Our Ovens"
        :title="$title"
        :subtitle="$subtitle"
    />

    @if($featuredProducts->count() >= 1)
        {{-- Featured first product --}}
        <div class="mb-10">
            <x-storefront.product-card :product="$featuredProducts->first()" variant="featured" />
        </div>
    @endif

    @if($featuredProducts->count() > 1)
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredProducts->skip(1) as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    @endif

    <div class="text-center mt-14">
        <x-storefront.button href="{{ route('storefront.menu') }}" variant="dark" size="lg" icon="arrow" class="group">
            View Full Menu
        </x-storefront.button>
    </div>
</x-storefront.section>
@endif
