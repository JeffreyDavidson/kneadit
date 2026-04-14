@props([
    'product',
    'cardClass' => '',
    'descriptionClass' => '',
])

<div class="{{ $cardClass }}">
    <div class="relative overflow-hidden aspect-[4/3]">
        @if ($product->image)
            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        @else
            <x-storefront.image-placeholder :name="$product->name" text-size="text-6xl" />
        @endif
        <div class="absolute top-4 right-4 px-4 py-2 rounded-full text-sm font-bold backdrop-blur-sm" style="background: rgba(28,20,16,0.8); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.2);">
            @money($product->price)
        </div>
        {{ $badge ?? '' }}
        {{ $overlay ?? '' }}
    </div>
    <div class="p-6">
        <h3 class="font-display text-xl font-semibold mb-1 text-warm-100">{{ $product->name }}</h3>
        @if ($product->description)
        <p class="text-sm leading-relaxed line-clamp-2 {{ $descriptionClass }} text-warm-500">{{ $product->description }}</p>
        @endif
        {{ $footer ?? '' }}
    </div>
</div>
