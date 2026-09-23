@props(['categories'])

{{-- Product Selection --}}
<div class="space-y-10 lg:col-span-2">
    <div class="flex items-center gap-4">
        <h2 class="font-display text-warm-100 text-2xl font-bold whitespace-nowrap">Select Your Items</h2>
        <div class="bg-warm-600/25 h-px flex-1"></div>
    </div>

    @foreach ($categories as $category)
        <div>
            <div class="mb-6 flex items-center gap-3">
                <span class="bg-warm-500 block h-px w-6"></span>
                <h3 class="font-display text-warm-300 text-xl font-semibold">{{ $category->name }}</h3>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($category->products as $product)
                    @if ($product->is_active)
                        <div
                            class="order-product-card"
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}"
                        >
                            {{-- Favorite Heart --}}
                            <button
                                type="button"
                                @click="toggleFavorite({{ $product->id }})"
                                class="bg-warm-900/60 absolute top-3 right-3 z-10 flex h-11 w-11 items-center justify-center rounded-full backdrop-blur-sm transition-all"
                                :class="isFavorite({{ $product->id }}) ? '' : 'hover:scale-110'"
                                :aria-label="isFavorite({{ $product->id }}) ? 'Remove ' + @js($product->name) + ' from favorites' : 'Add ' + @js($product->name) + ' to favorites'"
                            >
                                <x-heroicon-s-heart
                                    x-show="isFavorite({{ $product->id }})"
                                    class="h-5 w-5 text-red-500"
                                />
                                <x-heroicon-o-heart
                                    x-show="! isFavorite({{ $product->id }})"
                                    class="text-warm-300 h-5 w-5"
                                />
                            </button>

                            {{-- Product Image --}}
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
                                    />
                                @endif
                                {{-- Price badge --}}
                                <div class="bg-warm-900/80 text-warm-400 border-warm-500/20 absolute top-3 left-3 rounded-full border px-3 py-1.5 text-sm font-bold backdrop-blur-sm">
                                    @money($product->price)
                                </div>
                            </div>

                            <div class="p-5">
                                <h4 class="font-display text-warm-100 mb-1 text-lg font-semibold">
                                    {{ $product->name }}
                                </h4>
                                @if ($product->description)
                                    <p class="text-warm-500 mb-3 line-clamp-2 text-sm">{{ $product->description }}</p>
                                @endif
                                <div class="mt-2 flex items-center justify-between">
                                    <div
                                        x-show="getQuantity({{ $product->id }}) > 0"
                                        class="text-warm-400 text-sm font-medium"
                                    >
                                        <span x-text="getQuantity({{ $product->id }})"></span> in cart
                                    </div>
                                    <div x-show="getQuantity({{ $product->id }}) === 0"></div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="decrementItem({{ $product->id }})"
                                            :disabled="getQuantity({{ $product->id }}) <= 0"
                                            class="order-qty-btn"
                                        >
                                            −
                                        </button>
                                        <span
                                            class="text-warm-100 min-w-[1.5rem] text-center text-sm font-bold"
                                            x-text="getQuantity({{ $product->id }})"
                                        ></span>
                                        <button
                                            type="button"
                                            @click="incrementItem({{ $product->id }}, {{ $product->price?->dollars() ?? 0 }})"
                                            class="order-qty-btn"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
