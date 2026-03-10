@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
    $heroImage = \App\Models\Setting::get('hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
@endphp

<style>
    @keyframes reviewFadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes reviewKenBurns {
        0% { transform: scale(1); }
        100% { transform: scale(1.06); }
    }
    .review-fade-1 { animation: reviewFadeUp 0.8s ease-out 0.3s both; }
    .review-fade-2 { animation: reviewFadeUp 0.8s ease-out 0.5s both; }
    .review-fade-3 { animation: reviewFadeUp 0.8s ease-out 0.7s both; }
    .review-hero-img { animation: reviewKenBurns 20s ease-in-out infinite alternate; }
    .review-card {
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .review-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 50px rgba(28, 20, 16, 0.12);
    }
    .rating-bar-track {
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
    }
    .rating-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 1.2s ease-out;
    }
</style>

{{-- Photo-Forward Hero with Dark Overlay --}}
<section class="relative overflow-hidden" style="min-height: 60vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="{{ $storeName }} Reviews" class="w-full h-full object-cover review-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20" style="min-height: 60vh;">
        <div class="review-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">What People Say</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="review-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6" style="color: var(--warm-100);">
            Kind Words
        </h1>
        @if($totalReviews > 0)
        <p class="review-fade-2 font-script text-2xl md:text-3xl" style="color: var(--warm-400);">{{ $totalReviews }} {{ Str::plural('review', $totalReviews) }} from happy customers</p>
        @endif
    </div>
</section>

{{-- Stats Strip --}}
@if($totalReviews > 0)
<section style="background: var(--warm-800);">
    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-400);">{{ number_format($avgRating, 1) }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block" style="color: var(--warm-600);">Average Rating</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-400);">{{ $totalReviews }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block" style="color: var(--warm-600);">Total Reviews</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                @php
                    $fiveStarCount = $reviews->where('rating', 5)->count();
                    $fiveStarPct = $totalReviews > 0 ? round(($fiveStarCount / $totalReviews) * 100) : 0;
                @endphp
                <span class="block font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-400);">{{ $fiveStarPct }}%</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block" style="color: var(--warm-600);">5-Star Reviews</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-400);">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="color: {{ $i <= round($avgRating) ? 'var(--warm-500)' : 'rgba(139,104,68,0.3)' }};">★</span>
                    @endfor
                </span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block" style="color: var(--warm-600);">Overall</span>
            </div>
        </div>
    </div>
</section>
@endif

@if($reviews->count() > 0)

{{-- Featured Review: Massive Pull-Quote --}}
@php $featured = $reviews->first(); @endphp
<section class="relative py-24 md:py-32 overflow-hidden" style="background: var(--warm-100);">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="font-display font-bold leading-none mb-6" style="font-size: 8rem; color: var(--warm-500); opacity: 0.12; line-height: 0.5;">&ldquo;</div>
        @if($featured->comment)
        <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-10" style="color: var(--warm-800); letter-spacing: -0.01em;">
            {{ $featured->comment }}
        </blockquote>
        @endif
        <div class="flex justify-center gap-1 mb-4">
            @for($i = 1; $i <= 5; $i++)
            <svg class="w-5 h-5" style="color: {{ $i <= $featured->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            @endfor
        </div>
        <p class="font-semibold text-lg" style="color: var(--warm-700);">{{ $featured->customer_name }}</p>
        @if($featured->product)
        <p class="text-sm mt-1" style="color: var(--warm-500);">on {{ $featured->product->name }}</p>
        @endif
    </div>
</section>

{{-- Rating Distribution --}}
<section class="relative py-20 overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(212,146,12,0.06), transparent 60%);"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Rating Breakdown</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
        </div>

        <div class="space-y-4">
            @for($star = 5; $star >= 1; $star--)
            @php
                $count = $reviews->where('rating', $star)->count();
                $pct = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
            @endphp
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 flex-shrink-0" style="width: 80px;">
                    <span class="font-display font-semibold" style="color: var(--warm-300);">{{ $star }}</span>
                    <svg class="w-4 h-4" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div class="flex-1 rating-bar-track" style="background: var(--warm-800);">
                    <div class="rating-bar-fill" style="width: {{ $pct }}%; background: var(--warm-500);"></div>
                </div>
                <span class="text-sm font-medium flex-shrink-0" style="color: var(--warm-400); width: 40px; text-align: right;">{{ $count }}</span>
            </div>
            @endfor
        </div>
    </div>
</section>

{{-- All Reviews Grid --}}
<section class="relative py-24 md:py-28" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-6 mb-16">
            <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">All Reviews</span>
            <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mb-16">
            @foreach($reviews->skip(1) as $review)
            <div class="review-card p-8 rounded-2xl" style="background: white; border: 1px solid var(--warm-200);">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center font-display font-bold text-lg" style="background: var(--warm-200); color: var(--warm-600);">
                        {{ strtoupper(substr($review->customer_name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-lg" style="color: var(--warm-900);">{{ $review->customer_name }}</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4" style="color: {{ $i <= $review->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            <span class="text-xs" style="color: var(--warm-400);">{{ $review->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
                @if($review->comment)
                <p class="text-base leading-relaxed mb-4" style="color: var(--warm-700);">&ldquo;{{ $review->comment }}&rdquo;</p>
                @endif
                @if($review->product)
                <p class="text-sm font-medium" style="color: var(--warm-500);">{{ $review->product->name }}</p>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $reviews->links() }}
        </div>
    </div>
</section>

@else
{{-- Empty state --}}
<section class="relative py-24" style="background: var(--warm-100);">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="font-display font-bold leading-none mb-4" style="font-size: 5rem; color: var(--warm-300); opacity: 0.3; line-height: 0.6;">&ldquo;</div>
        <p class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: var(--warm-800);">No reviews yet</p>
        <p class="text-lg leading-relaxed" style="color: var(--warm-600);">Be the first to share your experience.</p>
    </div>
</section>
@endif

{{-- Leave a Review CTA --}}
<section class="relative py-24 overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(212,146,12,0.08), transparent 60%);"></div>
    <div class="relative z-10 text-center max-w-2xl mx-auto px-4">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-500);">Enjoyed something?</p>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-4" style="color: var(--warm-100);">
            We'd love to hear about it.
        </h2>
        <p class="text-lg mb-10" style="color: var(--warm-400);">
            Your feedback helps us bake better and helps others discover their next favorite treat.
        </p>
        <a href="{{ route('order.track') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
            Leave a Review
        </a>
    </div>
</section>
@endsection
