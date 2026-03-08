@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $tagline = \App\Models\Setting::get('business_tagline');
    $aboutUs = \App\Models\Setting::get('about_us_text');
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $socialLinks = json_decode(\App\Models\Setting::get('social_media_links', '{}'), true);
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take(3)->get();
    try {
        $customerPhotos = \App\Models\CustomerPhoto::approved()->featured()->with('product')->latest()->take(4)->get();
    } catch (\Exception $e) {
        $customerPhotos = collect();
    }
    $featuredProducts = \App\Models\Product::where('is_active', true)->take(6)->get();
@endphp

<!-- Hero — full viewport, dark overlay, bold typography -->
<section class="relative flex items-center justify-center overflow-hidden" style="min-height: 85vh; background: var(--warm-900);">
    <!-- Gradient background with warmth -->
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(212, 146, 12, 0.15), transparent 60%), radial-gradient(ellipse at 70% 80%, rgba(139, 104, 68, 0.1), transparent 50%);"></div>
    
    <!-- Subtle grain texture -->
    <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <p class="font-script text-2xl md:text-3xl mb-6" style="color: var(--warm-500);">Welcome to</p>
        <h1 class="font-display text-6xl md:text-8xl font-bold mb-6 leading-tight" style="color: var(--warm-100);">
            {{ $storeName }}
        </h1>
        <div class="w-24 h-1 mx-auto mb-8" style="background: linear-gradient(to right, transparent, var(--warm-500), transparent);"></div>
        <p class="font-script text-xl md:text-2xl mb-10" style="color: var(--warm-400);">
            {{ $tagline ?: 'Where artisan dreams rise to perfection' }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.create') }}" class="inline-block text-lg px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                Place Your Order
            </a>
            <a href="{{ route('storefront.menu') }}" class="inline-block text-lg px-10 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: transparent; color: var(--warm-200); border: 2px solid var(--warm-600);">
                Browse Our Menu
            </a>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </div>
</section>

<!-- About Strip -->
@if($aboutUs)
<section class="py-16 px-4" style="background: var(--warm-100);">
    <div class="max-w-3xl mx-auto text-center">
        <p class="text-lg md:text-xl leading-relaxed" style="color: var(--warm-700);">
            {{ $aboutUs }}
        </p>
    </div>
</section>
@endif

<!-- Featured Products — horizontal scroll on mobile, grid on desktop -->
@if($featuredProducts->isNotEmpty())
<section class="py-20 px-4" style="background: var(--warm-200);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Freshly made</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">Our Favorites</h2>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredProducts as $product)
            <div class="group rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl" style="background: white;">
                <!-- Product Image -->
                <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                            <span class="text-5xl font-display font-bold" style="color: var(--warm-400); opacity: 0.6;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <!-- Price badge -->
                    <div class="absolute top-4 right-4 px-4 py-1.5 rounded-full text-sm font-bold" style="background: var(--warm-900); color: var(--warm-400);">
                        ${{ number_format($product->price, 2) }}
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="font-display text-xl font-semibold mb-2" style="color: var(--warm-900);">
                        {{ $product->name }}
                    </h3>
                    @if($product->description)
                    <p class="text-sm leading-relaxed line-clamp-2" style="color: var(--warm-600);">
                        {{ $product->description }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('storefront.menu') }}" class="inline-flex items-center gap-2 font-display text-lg font-semibold transition-colors hover:underline" style="color: var(--warm-700);">
                View Full Menu
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Categories Breakdown -->
@if(isset($categories) && $categories->isNotEmpty())
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Something for everyone</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">What We Bake</h2>
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

<!-- Reviews Section — dark background for contrast -->
@if($reviews->count() > 0)
<section class="py-20 px-4" style="background: var(--warm-900);">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">What our customers say</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-100);">Kind Words</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($reviews as $review)
            <div class="rounded-2xl p-8 text-center" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(139, 104, 68, 0.2);">
                <div class="flex justify-center mb-5">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= $review->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="italic leading-relaxed mb-6" style="color: var(--warm-300);">
                    "{{ $review->comment }}"
                </p>
                <p class="font-semibold" style="color: var(--warm-400);">{{ $review->customer_name }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.reviews') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-500);">
                Read All Reviews →
            </a>
        </div>
    </div>
</section>
@endif

<!-- Customer Gallery -->
@if($customerPhotos->count() > 0)
<section class="py-20 px-4" style="background: var(--warm-200);">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Shared by our community</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">Customer Gallery</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($customerPhotos as $photo)
            <div class="rounded-2xl overflow-hidden group" style="background: white;">
                <div class="overflow-hidden">
                    <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}" 
                         alt="Photo by {{ $photo->customer_name }}" 
                         class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                </div>
                <div class="p-4">
                    @if($photo->caption)
                    <p class="text-sm italic mb-2" style="color: var(--warm-700);">"{{ Str::limit($photo->caption, 60) }}"</p>
                    @endif
                    <p class="text-sm font-semibold" style="color: var(--warm-900);">— {{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.gallery') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-700);">
                View Full Gallery →
            </a>
        </div>
    </div>
</section>
@endif

<!-- Latest Blog Posts -->
@php
    try {
        $latestPosts = \App\Models\BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();
    } catch (\Exception $e) {
        $latestPosts = collect();
    }
@endphp
@if($latestPosts->isNotEmpty())
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">From our kitchen</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-900);">Latest Updates</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($latestPosts as $blogPost)
            <a href="{{ route('storefront.blog.show', $blogPost->slug) }}" class="group rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl" style="background: white; border: 1px solid var(--warm-200);">
                @if($blogPost->featured_image)
                <div class="overflow-hidden" style="aspect-ratio: 16/9;">
                    <img src="{{ Storage::disk('public')->url($blogPost->featured_image) }}" alt="{{ $blogPost->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                </div>
                @else
                <div class="flex items-center justify-center" style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                    <svg class="w-10 h-10" style="color: var(--warm-500); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                @endif
                <div class="p-6">
                    <p class="text-xs font-medium mb-2" style="color: var(--warm-500);">{{ $blogPost->published_at->format('M j, Y') }}</p>
                    <h3 class="font-display text-lg font-semibold mb-2 group-hover:underline" style="color: var(--warm-900);">{{ $blogPost->title }}</h3>
                    @if($blogPost->excerpt)
                    <p class="text-sm line-clamp-2" style="color: var(--warm-600);">{{ $blogPost->excerpt }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-700);">
                View All Posts →
            </a>
        </div>
    </div>
</section>
@endif

<!-- Final CTA — full width with personality -->
<section class="py-24 px-4 text-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-900));">
    <div class="max-w-3xl mx-auto">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-500);">What are you waiting for?</p>
        <h2 class="font-display text-4xl md:text-5xl font-bold mb-6" style="color: var(--warm-100);">
            Treat Yourself Today
        </h2>
        <p class="text-lg mb-10" style="color: var(--warm-400);">
            Place your order and taste the difference that passion and craftsmanship make. 
            We require {{ $leadTimeHours }} hours notice to ensure the highest quality.
        </p>
        <a href="{{ route('order.create') }}" class="inline-block text-lg px-12 py-5 rounded-full font-bold transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
            Start Your Order
        </a>
    </div>
</section>

@if(!empty(array_filter($socialLinks ?? [])))
<!-- Social Follow Strip -->
<section class="py-12 px-4" style="background: var(--warm-100);">
    <div class="flex flex-col items-center">
        <p class="font-script text-lg mb-4" style="color: var(--warm-500);">Follow us</p>
        <div class="flex gap-4">
            @if(!empty($socialLinks['facebook']))
            <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener" class="w-11 h-11 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110" style="background: var(--warm-200); color: var(--warm-600);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['instagram']))
            <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener" class="w-11 h-11 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110" style="background: var(--warm-200); color: var(--warm-600);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            @endif
            @if(!empty($socialLinks['twitter']))
            <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener" class="w-11 h-11 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110" style="background: var(--warm-200); color: var(--warm-600);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            @endif
        </div>
    </div>
</section>
@endif
@endsection
