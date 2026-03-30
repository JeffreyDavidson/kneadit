@if($categories->isNotEmpty())
<section class="relative py-20 px-4 overflow-hidden" style="background: var(--warm-200);">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-14">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Explore</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
            <h2 class="font-display text-3xl md:text-5xl font-bold" style="color: var(--warm-900);">{{ $title }}</h2>
        </div>

        {{-- Category cards grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('storefront.menu') }}"
               class="group relative rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
               style="background: var(--warm-900); aspect-ratio: 1/1;">

                {{-- Background gradient with ghost letter --}}
                <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="font-display font-bold" style="font-size: 6rem; color: var(--warm-600); opacity: 0.15;">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                </div>

                {{-- Overlay --}}
                <div class="absolute inset-0 transition-opacity duration-300" style="background: linear-gradient(to top, rgba(28,20,16,0.9) 0%, rgba(28,20,16,0.2) 60%); opacity: 0.8;"></div>
                <div class="absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100" style="background: rgba(212,146,12,0.15);"></div>

                {{-- Content --}}
                <div class="absolute bottom-0 left-0 right-0 p-5">
                    <h3 class="font-display text-lg md:text-xl font-semibold mb-1" style="color: var(--warm-100);">{{ $category->name }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium" style="color: var(--warm-400);">{{ $category->products_count }} {{ Str::plural('item', $category->products_count ?? 0) }}</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-1" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
