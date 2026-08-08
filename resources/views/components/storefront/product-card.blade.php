@props([
    'product',
    'cardClass' => '',
    'descriptionClass' => '',
])

<div class="{{ $cardClass }}">
    <div class="relative aspect-[4/3] overflow-hidden">
        @if ($product->image)
            <img
                src="{{ Storage::url($product->image) }}"
                alt="{{ $product->name }}"
                class="h-full w-full object-cover"
            />
        @else
            <x-storefront.image-placeholder
                :name="$product->name"
                :category="$product->category?->name"
                text-size="text-6xl"
            />
        @endif
        <div class="bg-warm-900/80 text-warm-400 border-warm-500/20 absolute top-4 right-4 rounded-full border px-4 py-2 text-sm font-bold backdrop-blur-sm">
            @money($product->price)
        </div>
        {{ $badge ?? '' }} {{ $overlay ?? '' }}
    </div>
    <div class="p-6">
        <h3 class="font-display text-warm-100 mb-1 text-xl font-semibold">{{ $product->name }}</h3>
        @if ($product->description)
            <p class="text-sm leading-relaxed line-clamp-2 {{ $descriptionClass }} text-warm-500">
                {{ $product->description }}
            </p>
        @endif
        {{ $footer ?? '' }}
    </div>
</div>
