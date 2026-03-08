@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
    $aboutUs = \App\Models\Setting::get('about_us_text');
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $socialLinks = json_decode(\App\Models\Setting::get('social_media_links', '{}'), true);
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take(3)->get();
    $customerPhotos = \App\Models\CustomerPhoto::approved()->featured()->with('product')->latest()->take(4)->get();
@endphp

<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-20">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-600);">Welcome to</p>
        <h1 class="font-display text-5xl md:text-6xl font-bold mb-6" style="color: var(--warm-900);">
            {{ $storeName }}
        </h1>
        <p class="font-script text-2xl mb-8" style="color: var(--warm-600);">
            {{ $tagline ?: 'Where artisan dreams rise to perfection' }}
        </p>
        <p class="text-lg max-w-3xl mx-auto leading-relaxed mb-10" style="color: var(--warm-700);">
            @if($aboutUs)
                {{ $aboutUs }}
            @else
                Discover our carefully crafted selection of artisan breads, pastries, and custom creations. 
                Each item is made with passion, premium ingredients, and time-honored techniques.
            @endif
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
                        
                        <!-- Product Image -->
                        <div class="mb-4 rounded-lg overflow-hidden" style="aspect-ratio: 4/3;">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, #4a3728, #8b6844);">
                                    <span class="text-4xl font-display font-bold" style="color: #faf4e8;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>

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

    <!-- Customer Gallery Section -->
    @if($customerPhotos->count() > 0)
    <div class="mb-20">
        <div class="text-center mb-12">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Shared by our community</p>
            <h2 class="font-display text-3xl md:text-4xl font-semibold" style="color: var(--warm-900);">Customer Gallery</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($customerPhotos as $photo)
            <div class="card overflow-hidden">
                <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}" 
                     alt="Photo by {{ $photo->customer_name }}" 
                     class="w-full h-48 object-cover" loading="lazy">
                <div class="p-4">
                    @if($photo->caption)
                    <p class="text-sm italic mb-2" style="color: var(--warm-700);">"{{ Str::limit($photo->caption, 60) }}"</p>
                    @endif
                    <p class="text-sm font-semibold" style="color: var(--warm-900);">— {{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('storefront.gallery') }}" class="btn-secondary inline-block px-6 py-3">
                View Full Gallery →
            </a>
        </div>
    </div>
    @endif

    <!-- Latest Blog Posts -->
    @php
        $latestPosts = \App\Models\BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();
    @endphp
    @if($latestPosts->isNotEmpty())
    <div class="mb-20">
        <div class="text-center mb-10">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">From our kitchen</p>
            <h2 class="font-display text-3xl md:text-4xl font-semibold" style="color: var(--warm-900);">Latest Updates</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestPosts as $blogPost)
                <a href="{{ route('storefront.blog.show', $blogPost->slug) }}" class="group block rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" style="background: var(--warm-50); border: 1px solid var(--warm-200);">
                    @if($blogPost->featured_image)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ Storage::disk('public')->url($blogPost->featured_image) }}" alt="{{ $blogPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="aspect-video flex items-center justify-center" style="background: var(--warm-100);">
                            <svg class="w-10 h-10" style="color: var(--warm-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h3 class="font-display text-lg font-semibold group-hover:underline" style="color: var(--warm-900);">{{ $blogPost->title }}</h3>
                        @if($blogPost->excerpt)
                            <p class="text-sm mt-2 line-clamp-2" style="color: var(--warm-600);">{{ $blogPost->excerpt }}</p>
                        @endif
                        <p class="text-xs mt-3" style="color: var(--warm-500);">{{ $blogPost->published_at->format('M j, Y') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-6">
            <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-1 font-display text-sm hover:underline" style="color: var(--warm-600);">
                View All Posts →
            </a>
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
                We require {{ $leadTimeHours }} hours notice for all orders to ensure the highest quality.
            </p>
            <a href="{{ route('order.create') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                Start Your Order
            </a>
        </div>
    </div>

    @if(!empty(array_filter($socialLinks)))
    <!-- Social Media Links -->
    <div class="text-center mt-12">
        <p class="font-script text-xl mb-4" style="color: var(--warm-500);">Follow us</p>
        <div class="flex justify-center gap-6">
            @if(!empty($socialLinks['facebook']))
            <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['instagram']))
            <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['twitter']))
            <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: var(--warm-200); color: var(--warm-600);" onmouseover="this.style.background='var(--warm-600)';this.style.color='white'" onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-600)'">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            @endif
        </div>
    </div>
    @endif
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