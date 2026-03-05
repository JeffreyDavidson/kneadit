@extends('layouts.storefront')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-16">
        <h1 class="font-display text-5xl md:text-6xl font-bold text-warm-900 mb-6">
            Welcome to KneadIt
        </h1>
        <p class="font-script text-2xl text-warm-600 mb-8">
            Where artisan dreams rise to perfection
        </p>
        <p class="text-lg text-warm-700 max-w-3xl mx-auto leading-relaxed mb-8">
            Discover our carefully crafted selection of artisan breads, pastries, and custom creations. 
            Each item is made with passion, premium ingredients, and time-honored techniques.
        </p>
        <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
            Place Your Order
        </a>
    </div>

    <!-- Menu Categories -->
    <div class="grid gap-8">
        @foreach($categories as $category)
        <div class="card p-8">
            <div class="flex items-center mb-6">
                <h2 class="font-display text-3xl font-semibold text-warm-900">
                    {{ $category->name }}
                </h2>
                @if($category->description)
                <p class="ml-6 text-warm-700 italic">{{ $category->description }}</p>
                @endif
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($category->products as $product)
                <div class="border border-warm-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 relative" 
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
                            class="absolute top-4 right-4 text-2xl transition-colors duration-200"
                            :class="isFavorite ? 'text-red-500' : 'text-warm-300 hover:text-red-400'">
                        <span x-text="isFavorite ? '❤️' : '🤍'"></span>
                    </button>
                    
                    <div class="pr-8">
                        <h3 class="font-display text-xl font-semibold text-warm-900 mb-2">
                            {{ $product->name }}
                        </h3>
                        
                        @if($product->description)
                        <p class="text-warm-700 mb-3 leading-relaxed">
                            {{ $product->description }}
                        </p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xl text-warm-600">
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

    <!-- Call to Action -->
    <div class="text-center mt-16">
        <div class="bg-warm-200 rounded-2xl p-12">
            <h2 class="font-display text-3xl font-semibold text-warm-900 mb-4">
                Ready to Experience Artisan Excellence?
            </h2>
            <p class="text-warm-700 text-lg mb-8 max-w-2xl mx-auto">
                Place your order today and taste the difference that passion and craftsmanship make. 
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
    // Listen for email changes from other tabs/windows
    window.addEventListener('storage', function(e) {
        if (e.key === 'customer_email') {
            // Reload favorites for all products
            window.dispatchEvent(new CustomEvent('email-changed', { 
                detail: { email: e.newValue }
            }));
        }
    });
</script>
@endsection