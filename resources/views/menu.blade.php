@extends('layouts.storefront')

@section('content')
<style>
    .menu-category-nav {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .menu-category-nav::-webkit-scrollbar { display: none; }
    .menu-category-nav a {
        transition: color 0.2s, border-color 0.2s;
    }
    .menu-product-row {
        transition: background 0.2s;
    }
    .menu-product-row:hover {
        background: var(--warm-100);
    }
    .menu-product-image {
        transition: transform 0.4s ease;
    }
    .menu-product-row:hover .menu-product-image {
        transform: scale(1.05);
    }
</style>

{{-- Editorial hero --}}
<div class="py-20 md:py-28 text-center" style="background: var(--warm-100);">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight leading-none mb-6" style="color: var(--warm-900);">
            The Menu
        </h1>
        <p class="text-lg md:text-xl max-w-2xl mx-auto leading-relaxed" style="color: var(--warm-600);">
            Everything we make, made with care. Browse at your pace — when something catches your eye, we'll have it freshly prepared just for you.
        </p>
    </div>
</div>

{{-- Sticky category nav --}}
@if(count($categories) > 1)
<div class="sticky top-0 z-30 border-b" style="background: var(--warm-50); border-color: var(--warm-200);">
    <div class="max-w-6xl mx-auto">
        <nav class="menu-category-nav flex overflow-x-auto gap-1 px-4 py-3">
            @foreach($categories as $cat)
            <a href="#category-{{ $cat->id }}" 
               class="whitespace-nowrap text-sm font-medium px-4 py-2 rounded-full border-2 border-transparent"
               style="color: var(--warm-600);"
               onmouseover="this.style.color='var(--warm-900)'"
               onmouseout="this.style.color='var(--warm-600)'">
                {{ $cat->name }}
            </a>
            @endforeach
        </nav>
    </div>
</div>
@endif

<div class="max-w-6xl mx-auto px-4">
    @forelse($categories as $category)
    <section id="category-{{ $category->id }}" class="py-16 md:py-20">
        {{-- Category divider --}}
        <div class="mb-12 md:mb-16">
            <div class="flex items-center gap-6">
                <h2 class="font-display text-3xl md:text-5xl font-bold whitespace-nowrap" style="color: var(--warm-900);">
                    {{ $category->name }}
                </h2>
                <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
            </div>
            @if($category->description)
            <p class="mt-3 text-lg" style="color: var(--warm-600);">{{ $category->description }}</p>
            @endif
        </div>

        {{-- Products as editorial rows --}}
        <div class="space-y-0">
            @foreach($category->products as $index => $product)
            <div class="menu-product-row rounded-xl px-4 md:px-6 py-6 md:py-8 {{ $index > 0 ? 'border-t' : '' }}" style="border-color: var(--warm-200);">
                <div class="flex flex-col md:flex-row md:items-center gap-5 md:gap-8">
                    {{-- Image --}}
                    <div class="w-full md:w-28 md:h-28 flex-shrink-0 rounded-lg overflow-hidden" style="aspect-ratio: 1/1;">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="menu-product-image w-full h-full object-cover">
                        @else
                            <div class="menu-product-image w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-600), var(--warm-500));">
                                <span class="text-3xl md:text-2xl font-display font-bold" style="color: var(--warm-200);">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mb-1">
                            <h3 class="font-display text-xl md:text-2xl font-semibold" style="color: var(--warm-900);">
                                {{ $product->name }}
                            </h3>
                            @if($product->seasonal_badge)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: var(--warm-200); color: var(--warm-600);">{{ $product->seasonal_badge }}</span>
                            @endif
                        </div>
                        @if($product->description)
                        <p class="leading-relaxed" style="color: var(--warm-600);">{{ $product->description }}</p>
                        @endif
                    </div>

                    {{-- Price & availability --}}
                    <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-end md:gap-2">
                        <span class="font-display text-2xl md:text-3xl font-bold" style="color: var(--warm-800);">
                            ${{ number_format($product->price, 2) }}
                        </span>
                        @if(!$product->is_available)
                        <div x-data="{ showWaitlist: false, submitted: false }" class="text-right">
                            <button x-show="!showWaitlist && !submitted" @click="showWaitlist = true"
                                class="text-sm font-medium px-3 py-1 rounded-full cursor-pointer transition-colors"
                                style="color: var(--warm-600); background: var(--warm-200);"
                                onmouseover="this.style.background='var(--warm-300)'"
                                onmouseout="this.style.background='var(--warm-200)'">
                                🔔 Notify Me
                            </button>
                            <span x-show="submitted" class="text-green-600 text-sm font-medium">✓ We'll notify you!</span>
                            <form x-show="showWaitlist" @submit.prevent="fetch('{{ route('product-waitlist.join') }}', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({product_id:{{ $product->id }},customer_email:$refs.email.value})}).then(()=>{submitted=true;showWaitlist=false})" class="flex gap-1 mt-1">
                                <input x-ref="email" type="email" required placeholder="Email" class="text-sm rounded-lg border px-2 py-1 w-36" style="border-color: var(--warm-300);">
                                <button type="submit" class="text-sm px-2 py-1 rounded-lg text-white" style="background: var(--warm-600);">Go</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @empty
    <div class="py-24 text-center">
        <p class="font-display text-2xl" style="color: var(--warm-600);">Our menu is being updated. Check back soon.</p>
    </div>
    @endforelse

    {{-- CTA --}}
    <div class="py-16 md:py-24">
        <div class="text-center max-w-2xl mx-auto">
            <p class="font-script text-xl mb-4" style="color: var(--warm-500);">Ready?</p>
            <h2 class="font-display text-3xl md:text-5xl font-bold mb-6" style="color: var(--warm-900);">
                Let's get baking.
            </h2>
            <p class="text-lg mb-10" style="color: var(--warm-600);">
                All orders need {{ \App\Models\Setting::get('order_lead_time_hours', '24') }} hours notice. Place yours now.
            </p>
            <a href="{{ route('order.create') }}" 
               class="btn-primary text-lg px-10 py-4 inline-block">
                Place an Order
            </a>
        </div>
    </div>
</div>
@endsection