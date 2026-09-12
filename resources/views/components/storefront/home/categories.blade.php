@if ($categories->isNotEmpty())
    <section class="bg-warm-200 relative overflow-hidden px-4 py-20">
        <div class="mx-auto max-w-6xl">
            {{-- Header --}}
            <div class="mb-14 text-center">
                <x-storefront.eyebrow line-opacity="0.5" class="mb-4">Explore</x-storefront.eyebrow>
                <h2 class="font-display text-warm-900 text-3xl font-bold md:text-5xl">{{ $title }}</h2>
            </div>

            {{-- Category cards grid --}}
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('storefront.menu') }}"
                        class="group bg-warm-900 relative aspect-square overflow-hidden rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >
                        {{-- Background gradient with ghost letter --}}
                        <div class="from-warm-800 to-warm-700 absolute inset-0 bg-gradient-to-br"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="font-display text-warm-600 text-[6rem] font-bold opacity-15">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                        </div>

                        {{-- Overlay --}}
                        <div class="from-warm-900/90 via-warm-900/40 absolute inset-0 bg-gradient-to-t to-transparent opacity-80 transition-opacity duration-300"></div>
                        <div class="bg-warm-500/15 absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>

                        {{-- Content --}}
                        <div class="absolute right-0 bottom-0 left-0 p-5">
                            <h3 class="font-display text-warm-100 mb-1 text-lg font-semibold md:text-xl">
                                {{ $category->name }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="text-warm-400 text-xs font-medium">{{ $category->products_count }} {{ Str::plural('item', $category->products_count ?? 0) }}</span>
                                <x-heroicon-o-chevron-right
                                    class="text-warm-500 h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1"
                                    stroke-width="2"
                                />
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
