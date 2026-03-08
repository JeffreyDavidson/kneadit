@php
    $title = $config['title'] ?? 'What We Bake';
    $subtitle = $config['subtitle'] ?? 'Something for everyone';
@endphp
@if(isset($categories) && $categories->isNotEmpty())
<section class="py-16 px-4 overflow-hidden" style="background: var(--warm-100);">
    <div class="max-w-7xl mx-auto">
        <div class="section-divider mb-12"></div>
        <h2 class="font-display text-3xl md:text-4xl font-semibold text-center mb-10" style="color: var(--warm-900);">{{ $title }}</h2>

        <div class="flex gap-6 md:gap-10 overflow-x-auto pb-4 justify-start md:justify-center" style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
            <style>.categories-strip::-webkit-scrollbar { display: none; }</style>
            @foreach($categories as $category)
            <a href="{{ route('storefront.menu') }}" class="categories-strip flex-shrink-0 group text-center transition-all duration-200" style="min-width: 120px;">
                <span class="font-display text-xl md:text-2xl font-semibold block transition-colors duration-200 whitespace-nowrap" style="color: var(--warm-700);" onmouseover="this.style.color='var(--warm-500)'" onmouseout="this.style.color='var(--warm-700)'">{{ $category->name }}</span>
                <span class="block text-xs mt-1 font-medium" style="color: var(--warm-500);">{{ $category->products_count ?? $category->products->count() }} items</span>
            </a>
            @if(!$loop->last)
            <span class="flex-shrink-0 self-center" style="color: var(--warm-400); opacity: 0.3;">&middot;</span>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif
