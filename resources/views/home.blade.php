@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take(3)->get();
@endphp

<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-20">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-600);">Welcome to</p>
        <h1 class="font-display text-5xl md:text-6xl font-bold mb-6" style="color: var(--warm-900);">
            {{ $storeName }}
        </h1>
        <p class="font-script text-2xl mb-8" style="color: var(--warm-600);">
            Where artisan dreams rise to perfection
        </p>
        <p class="text-lg max-w-3xl mx-auto leading-relaxed mb-10" style="color: var(--warm-700);">
            Discover our carefully crafted selection of artisan breads, pastries, and custom creations. 
            Each item is made with passion, premium ingredients, and time-honored techniques.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                Place Your Order
            </a>
            <a href="{{ route('storefront.menu') }}" class="btn-secondary text-lg px-8 py-4 inline-block">
                Browse Our Menu
            </a>
        </div>
    </div>

    <!-- Featured Categories & Products -->
    <div class="mb-20">
        <div class="text-center mb-12">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Freshly made</p>
            <h2 class="font-display text-3xl md:text-4xl font-semibold" style="color: var(--warm-900);">Our Offerings</h2>
        </div>
        
        <div class="grid gap-10">
            @foreach($categories as $category)
            <div class="card p-8">
                <div class="flex flex-col sm:flex-row sm:items-center mb-6">
                    <h2 class="font-display text-3xl font-semibold" style="color: var(--warm-900);">
                        {{ $category->name }}
                    </h2>
                    @if($category->description)
                    <p class="sm:ml-6 mt-1 sm:mt-0 italic" style="color: var(--warm-700);">{{ $category->description }}</p>
                    @endif
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($category->products as $product)
                    <div class="border rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 relative" 
                         style="border-color: var(--warm-200);"
                         x-data="{ 
                             email: localStorage.getItem('customer_email') || '',
                             isFavorite: false,
                             async toggleFavorite() {
                                 if (!this.email) {
                                     alert('Please enter your email in the order form to save favorites');
                                     return;
                                 }
                                 try {
                                     const response = await fetch('{{ route('favorites.toggle') }}', {
                                         method: 'POST',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                         },
                                         body: JSON.stringify({
                                             customer_email: this.email,
                                             product_id: {{ $product->id }}
                                         })
                                     });
                                     const data = await response.json();
                                     if (data.success) {
                                         this.isFavorite = data.is_favorite;
                                     }
                                 } catch (error) {
                                     console.error('Error toggling favorite:', error);
                                 }
                             },
                             async loadFavoriteStatus() {
                                 if (!this.email) return;
                                 try {
                                     const response = await fetch(`{{ route('favorites.get') }}?email=${encodeURIComponent(this.email)}`);
                                     const data = await response.json();
                                     this.isFavorite = data.favorites.includes({{ $product->id }});
                                 } catch (error) {
                                     console.error('Error loading favorites:', error);
                                 }
                             }
                         }"
                         x-init="
                             $watch('email', () => loadFavoriteStatus());
                             loadFavoriteStatus();
                             window.addEventListener('storage', (e) => {
                                 if (e.key === 'customer_email') {
                                     email = e.newValue || '';
                                 }
                             });
                         ">
                        
                        <!-- Favorite Heart -->
                        <button @click="toggleFavorite()" 
                                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full transition-colors duration-200"
                                :style="isFavorite ? 'color: #e53e3e' : 'color: var(--warm-300)'"
                                :class="!isFavorite && 'hover:text-red-400'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>
                        
                        <div class="pr-8">
                            <h3 class="font-display text-xl font-semibold mb-2" style="color: var(--warm-900);">
                                {{ $product->name }}
                            </h3>
                            
                            @if($product->description)
                            <p class="mb-3 leading-relaxed" style="color: var(--warm-700);">
                                {{ $product->description }}
                            </p>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xl" style="color: var(--warm-600);">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                                
                                @if($product->is_available)
                                <span class="text-green-600 text-sm font-medium">Available</span>
                                @else
                                <span class="text-red-600 text-sm font-medium">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Reviews Section -->
    @if($reviews->count() > 0)
    <div class="mb-20">
        <div class="text-center mb-12">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">What our customers say</p>
            <h2 class="font-display text-3xl md:text-4xl font-semibold" style="color: var(--warm-900);">Kind Words</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($reviews as $review)
            <div class="card p-8 text-center">
                <div class="flex justify-center mb-4">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= $review->rating ? '' : 'opacity-25' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="italic leading-relaxed mb-4" style="color: var(--warm-700);">
                    "{{ $review->comment }}"
                </p>
                <p class="font-semibold" style="color: var(--warm-900);">{{ $review->customer_name }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Call to Action -->
    <div class="text-center">
        <div class="rounded-2xl p-12" style="background: var(--warm-200);">
            <p class="font-script text-xl mb-3" style="color: var(--warm-500);">Don't wait</p>
            <h2 class="font-display text-3xl font-semibold mb-4" style="color: var(--warm-900);">
                Treat Yourself Today
            </h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: var(--warm-700);">
                Place your order and taste the difference that passion and craftsmanship make. 
                We require 48 hours notice for all orders to ensure the highest quality.
            </p>
            <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                Start Your Order
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('storage', function(e) {
        if (e.key === 'customer_email') {
            window.dispatchEvent(new CustomEvent('email-changed', { 
                detail: { email: e.newValue }
            }));
        }
    });
</script>
@endsection