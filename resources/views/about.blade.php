@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
    $aboutUs = \App\Models\Setting::get('about_us_text');
    $address = \App\Models\Setting::get('store_address');
    $allergyDisclaimer = \App\Models\Setting::get('allergy_disclaimer');
    $socialLinks = json_decode(\App\Models\Setting::get('social_media_links', '{}'), true);
@endphp

<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Hero -->
    <div class="text-center mb-20">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-600);">Get to know</p>
        <h1 class="font-display text-5xl md:text-6xl font-bold mb-6" style="color: var(--warm-900);">
            {{ $storeName }}
        </h1>
        <p class="font-script text-2xl" style="color: var(--warm-600);">
            {{ $tagline ?: 'Where artisan dreams rise to perfection' }}
        </p>
    </div>

    <!-- About Us -->
    <div class="card p-8 md:p-12 mb-12 max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Our story</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">About Us</h2>
        </div>
        <p class="text-lg leading-relaxed text-center" style="color: var(--warm-700);">
            @if($aboutUs)
                {{ $aboutUs }}
            @else
                We are passionate bakers dedicated to crafting the finest artisan breads, pastries, and treats. Every item that leaves our kitchen is made with love, premium ingredients, and time-honored techniques passed down through generations. We believe that great baking is both an art and a science, and we pour our hearts into every loaf, every pastry, and every bite.
            @endif
        </p>
    </div>

    <!-- Our Promise -->
    <div class="mb-12">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">What we stand for</p>
            <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">Our Promise</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: var(--warm-200);">
                    <svg class="w-8 h-8" style="color: var(--warm-600);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Quality Ingredients</h3>
                <p style="color: var(--warm-700);">We source only the finest, freshest ingredients for every recipe. No shortcuts, no compromises.</p>
            </div>

            <div class="card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: var(--warm-200);">
                    <svg class="w-8 h-8" style="color: var(--warm-600);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Freshly Baked</h3>
                <p style="color: var(--warm-700);">Everything is baked fresh for your order. We believe in delivering the best experience, every single time.</p>
            </div>

            <div class="card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: var(--warm-200);">
                    <svg class="w-8 h-8" style="color: var(--warm-600);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="font-display text-xl font-semibold mb-3" style="color: var(--warm-900);">Handmade with Love</h3>
                <p style="color: var(--warm-700);">Every product is handcrafted by skilled bakers who take pride in their craft and care about every detail.</p>
            </div>
        </div>
    </div>

    @if($allergyDisclaimer)
    <div class="card p-6 mb-12 max-w-4xl mx-auto" style="border-left: 4px solid var(--warm-500);">
        <p class="text-sm leading-relaxed" style="color: var(--warm-700);">
            <strong>Allergy Notice:</strong> {{ $allergyDisclaimer }}
        </p>
    </div>
    @endif

    <!-- Location -->
    @if($address)
    <div class="text-center mb-12">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Find us</p>
        <h2 class="font-display text-3xl font-semibold mb-6" style="color: var(--warm-900);">Our Location</h2>
        <div class="card p-8 max-w-lg mx-auto">
            <p class="text-lg" style="color: var(--warm-700);">{{ $address }}</p>
        </div>
    </div>
    @endif

    <!-- Social Links -->
    @if(!empty(array_filter($socialLinks ?? [])))
    <div class="text-center">
        <p class="font-script text-xl mb-4" style="color: var(--warm-500);">Follow us</p>
        <div class="flex justify-center gap-6">
            @if(!empty($socialLinks['facebook']))
            <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['instagram']))
            <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['twitter']))
            <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
