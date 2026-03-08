@php
    $title = $config['title'] ?? 'What We Bake';
    $subtitle = $config['subtitle'] ?? 'Something for everyone';
@endphp
@if(isset($categories) && $categories->isNotEmpty())
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">{{ $subtitle }}</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">{{ $title }}</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
            <a href="{{ route('storefront.menu') }}" class="group card p-8 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="border: 2px solid transparent;" onmouseover="this.style.borderColor='var(--warm-500)'" onmouseout="this.style.borderColor='transparent'">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: var(--warm-200);">
                    <span class="text-2xl font-display font-bold" style="color: var(--warm-600);">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                </div>
                <h3 class="font-display text-xl font-semibold mb-2" style="color: var(--warm-900);">{{ $category->name }}</h3>
                @if($category->description)
                <p class="text-sm" style="color: var(--warm-600);">{{ $category->description }}</p>
                @endif
                <p class="text-sm mt-3 font-medium" style="color: var(--warm-500);">{{ $category->products_count ?? $category->products->count() }} items →</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
