@extends('layouts.storefront')

@section('content')
<style>
    .menu-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .menu-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 60px rgba(28, 20, 16, 0.4);
    }
    .menu-card:hover img {
        transform: scale(1.08);
    }
    .menu-card img {
        transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .menu-card:hover .menu-card-overlay {
        opacity: 1;
    }
    .menu-card:hover .menu-card-cta {
        transform: translateY(0);
        opacity: 1;
    }
    .menu-card-overlay {
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .menu-card-cta {
        transform: translateY(10px);
        opacity: 0;
        transition: all 0.4s ease 0.1s;
    }
    .category-tab {
        transition: all 0.3s ease;
    }
    .category-tab.active {
        background: var(--warm-500) !important;
        color: var(--warm-900) !important;
    }
    @keyframes menuFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .menu-fade-1 { animation: menuFadeUp 0.7s ease-out 0.2s both; }
    .menu-fade-2 { animation: menuFadeUp 0.7s ease-out 0.4s both; }
    .menu-fade-3 { animation: menuFadeUp 0.7s ease-out 0.6s both; }
</style>

@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp

{{-- Dark Hero Banner --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); min-height: 45vh;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 80%, rgba(212,146,12,0.08), transparent 60%);"></div>

    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4" style="min-height: 45vh; padding-top: 8vh;">
        <div class="menu-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $storeName }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="menu-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6" style="color: var(--warm-100);">
            Our Menu
        </h1>
        <p class="menu-fade-2 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed" style="color: var(--warm-400);">
            Everything we make, crafted with care. Browse at your pace — when something catches your eye, we'll have it freshly prepared just for you.
        </p>
    </div>
</section>

{{-- Category Filter Tabs --}}
@if(count($categories) > 1)
<div class="sticky top-16 z-30" style="background: var(--warm-900); border-bottom: 1px solid rgba(139,104,68,0.15);">
    <div class="max-w-7xl mx-auto px-4">
        <nav class="flex overflow-x-auto gap-2 py-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;" x-data="{ active: '' }">
            <a href="#all" @click="active = ''"
               class="category-tab whitespace-nowrap text-sm font-semibold px-5 py-2.5 rounded-full"
               :class="active === '' ? 'active' : ''"
               style="color: var(--warm-400); background: rgba(139,104,68,0.15);">
                All
            </a>
            @foreach($categories as $cat)
            <a href="#category-{{ $cat->id }}" @click="active = '{{ $cat->id }}'"
               class="category-tab whitespace-nowrap text-sm font-semibold px-5 py-2.5 rounded-full"
               :class="active === '{{ $cat->id }}' ? 'active' : ''"
               style="color: var(--warm-400); background: rgba(139,104,68,0.15);">
                {{ $cat->name }}
            </a>
            @endforeach
        </nav>
    </div>
</div>
@endif

{{-- Product Grid by Category --}}
<div style="background: var(--warm-900);">
    <div class="max-w-7xl mx-auto px-4">
        @forelse($categories as $category)
        <section id="category-{{ $category->id }}" class="py-16 md:py-20">
            {{-- Category Header --}}
            <div class="mb-12">
                <div class="flex items-center gap-4 mb-3">
                    <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Collection</span>
                </div>
                <div class="flex items-end gap-6">
                    <h2 class="font-display text-3xl md:text-5xl font-bold" style="color: var(--warm-100);">
                        {{ $category->name }}
                    </h2>
                    <div class="flex-1 h-px mb-3" style="background: rgba(139,104,68,0.2);"></div>
                </div>
                @if($category->description)
                <p class="mt-3 text-lg" style="color: var(--warm-500);">{{ $category->description }}</p>
                @endif
            </div>

            {{-- Product Cards Grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($category->products as $product)
                <div class="menu-card" style="background: var(--warm-800);">
                    <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-800));">
                                <span class="text-6xl font-display font-bold" style="color: var(--warm-600); opacity: 0.3;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        {{-- Price badge --}}
                        <div class="absolute top-4 right-4 px-4 py-2 rounded-full text-sm font-bold backdrop-blur-sm" style="background: rgba(28,20,16,0.8); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.2);">
                            ${{ number_format($product->price, 2) }}
                        </div>
                        {{-- Seasonal badge --}}
                        @if($product->seasonal_badge)
                        <div class="absolute top-4 left-4 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider" style="background: var(--warm-500); color: var(--warm-900);">
                            {{ $product->seasonal_badge }}
                        </div>
                        @endif
                        {{-- Hover overlay --}}
                        <div class="menu-card-overlay absolute inset-0 flex items-center justify-center" style="background: rgba(28,20,16,0.5);">
                            @if($product->is_available)
                            <a href="{{ route('order.create') }}" class="menu-card-cta inline-block px-6 py-3 rounded-full text-sm font-semibold" style="background: var(--warm-500); color: var(--warm-900);">
                                Add to Order
                            </a>
                            @else
                            <span class="menu-card-cta inline-block px-6 py-3 rounded-full text-sm font-semibold" style="background: rgba(255,255,255,0.15); color: var(--warm-300); backdrop-filter: blur(4px);">
                                Currently Unavailable
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="font-display text-xl font-semibold mb-1" style="color: var(--warm-100);">{{ $product->name }}</h3>
                        @if($product->description)
                        <p class="text-sm leading-relaxed line-clamp-2 mb-3" style="color: var(--warm-500);">{{ $product->description }}</p>
                        @endif
                        @if(!$product->is_available)
                        <div x-data="{ showWaitlist: false, submitted: false }">
                            <button x-show="!showWaitlist && !submitted" @click="showWaitlist = true"
                                class="text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer transition-all duration-200"
                                style="color: var(--warm-400); background: rgba(139,104,68,0.2);">
                                🔔 Notify Me When Available
                            </button>
                            <span x-show="submitted" class="text-xs font-medium" style="color: var(--warm-400);">✓ We'll notify you!</span>
                            <form x-show="showWaitlist" @submit.prevent="fetch('{{ route('product-waitlist.join') }}', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({product_id:{{ $product->id }},customer_email:$refs.email{{ $product->id }}.value})}).then(()=>{submitted=true;showWaitlist=false})" class="flex gap-1 mt-2">
                                <input x-ref="email{{ $product->id }}" type="email" required placeholder="Email" class="text-sm rounded-lg border px-2 py-1 w-full" style="border-color: rgba(139,104,68,0.3); background: var(--warm-800); color: var(--warm-200);">
                                <button type="submit" class="text-sm px-3 py-1 rounded-lg font-semibold flex-shrink-0" style="background: var(--warm-500); color: var(--warm-900);">Go</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @empty
        <div class="py-24 text-center">
            <p class="font-display text-2xl" style="color: var(--warm-400);">Our menu is being updated. Check back soon.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- CTA Section --}}
<section class="relative py-24 overflow-hidden" style="background: var(--warm-800);">
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>
    <div class="relative z-10 text-center max-w-2xl mx-auto px-4">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-500);">Ready to order?</p>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-6" style="color: var(--warm-100);">
            Let's get baking.
        </h2>
        <p class="text-lg mb-10" style="color: var(--warm-400);">
            All orders need {{ \App\Models\Setting::get('order_lead_time_hours', '24') }} hours notice. Place yours now.
        </p>
        <a href="{{ route('order.create') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
            Place an Order
        </a>
    </div>
</section>
@endsection
