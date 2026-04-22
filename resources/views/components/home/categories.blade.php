@if ($categories->isNotEmpty())
<section class="relative py-20 px-4 overflow-hidden bg-warm-200">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-14">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px bg-warm-500 opacity-50"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">Explore</span>
                <span class="block w-8 h-px bg-warm-500 opacity-50"></span>
            </div>
            <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-900">{{ $title }}</h2>
        </div>

        {{-- Category cards grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($categories as $category)
            <a href="{{ route('storefront.menu') }}"
               class="group relative rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-warm-900 aspect-square">

                {{-- Background gradient with ghost letter --}}
                <div class="absolute inset-0 bg-gradient-to-br from-warm-800 to-warm-700"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="font-display font-bold text-[6rem] text-warm-600 opacity-15">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                </div>

                {{-- Overlay --}}
                <div class="absolute inset-0 transition-opacity duration-300 bg-gradient-to-t from-warm-900/90 via-warm-900/40 to-transparent opacity-80"></div>
                <div class="absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 bg-warm-500/15"></div>

                {{-- Content --}}
                <div class="absolute bottom-0 left-0 right-0 p-5">
                    <h3 class="font-display text-lg md:text-xl font-semibold mb-1 text-warm-100">{{ $category->name }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-warm-400">{{ $category->products_count }} {{ Str::plural('item', $category->products_count ?? 0) }}</span>
                        <x-heroicon-o-chevron-right class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-1 text-warm-500" stroke-width="2" />
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
