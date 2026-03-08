@php
    $title = $config['title'] ?? 'What We Bake';
@endphp
@if(isset($categories) && $categories->isNotEmpty())
<section class="py-16 px-4" style="background: var(--warm-100);">
    <div class="max-w-5xl mx-auto text-center">
        <p class="uppercase tracking-[0.2em] text-xs font-medium mb-10" style="color: var(--warm-500);">{{ $title }}</p>

        <div class="flex flex-wrap justify-center gap-x-2 gap-y-3">
            @foreach($categories as $category)
            <a href="{{ route('storefront.menu') }}" 
               class="inline-block px-6 py-2.5 rounded-full font-display text-base font-medium transition-all duration-200"
               style="color: var(--warm-700); border: 1px solid var(--warm-300); background: transparent;"
               onmouseover="this.style.background='var(--warm-500)';this.style.color='var(--warm-900)';this.style.borderColor='var(--warm-500)'"
               onmouseout="this.style.background='transparent';this.style.color='var(--warm-700)';this.style.borderColor='var(--warm-300)'">
                {{ $category->name }}
                <span class="text-xs ml-1 opacity-50">{{ $category->products_count ?? $category->products->count() }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
