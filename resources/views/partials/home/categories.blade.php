@php
    $title = $config['title'] ?? 'What We Bake';
    $subtitle = $config['subtitle'] ?? 'Something for everyone';
@endphp
@if(isset($categories) && $categories->isNotEmpty())
<x-storefront.section bg="light" padding="lg" maxWidth="5xl">
    <x-storefront.section-header
        eyebrow="Categories"
        :title="$title"
        :subtitle="$subtitle"
        align="center"
    />

    <div class="flex flex-wrap justify-center gap-3">
        @foreach($categories as $category)
        <a href="{{ route('storefront.menu') }}"
           class="group inline-flex items-center gap-2 px-6 py-3 rounded-full font-body text-base font-medium transition-all duration-300 hover:scale-105 hover:shadow-md"
           style="color: var(--warm-700); border: 1.5px solid var(--warm-300); background: transparent;"
           onmouseover="this.style.background='var(--warm-500)';this.style.color='#fff';this.style.borderColor='var(--warm-500)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--warm-700)';this.style.borderColor='var(--warm-300)'">
            {{ $category->name }}
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold transition-all duration-300" style="background: var(--warm-200); color: var(--warm-600);">
                {{ $category->products_count ?? $category->products->count() }}
            </span>
        </a>
        @endforeach
    </div>
</x-storefront.section>

<x-storefront.divider style="line" width="md" />
@endif
