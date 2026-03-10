@extends('layouts.storefront')

@section('content')
<style>
    .review-card {
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(28, 20, 16, 0.15);
    }
    .rating-bar {
        transition: width 1s ease-out;
    }
    @keyframes reviewFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .review-fade-1 { animation: reviewFadeUp 0.7s ease-out 0.2s both; }
    .review-fade-2 { animation: reviewFadeUp 0.7s ease-out 0.4s both; }
</style>

{{-- Dark Hero Banner --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); min-height: 45vh;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 80%, rgba(212,146,12,0.08), transparent 60%);"></div>

    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4" style="min-height: 45vh; padding-top: 8vh;">
        <div class="review-fade-1 flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">What People Say</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="review-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-8" style="color: var(--warm-100);">
            Kind Words
        </h1>

        {{-- Overall Rating Summary --}}
        @if($totalReviews > 0)
        <div class="review-fade-2 flex flex-col items-center">
            <span class="font-display text-6xl md:text-7xl font-bold" style="color: var(--warm-400);">{{ number_format($avgRating, 1) }}</span>
            <div class="flex gap-1 my-3">
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-6 h-6" style="color: {{ $i <= round($avgRating) ? 'var(--warm-500)' : 'rgba(139,104,68,0.3)' }};" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            <p class="text-sm" style="color: var(--warm-500);">from {{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
        </div>
        @endif
    </div>
</section>

<div class="max-w-5xl mx-auto px-4 py-16 md:py-24">
    @if($reviews->count() > 0)

    {{-- Featured Review: Massive Pull-Quote --}}
    @php $featured = $reviews->first(); @endphp
    <div class="mb-24 text-center max-w-4xl mx-auto">
        <div class="font-display font-bold leading-none mb-6" style="font-size: 6rem; color: var(--warm-500); opacity: 0.15; line-height: 0.6;">&ldquo;</div>
        @if($featured->comment)
        <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-8" style="color: var(--warm-800); letter-spacing: -0.01em;">
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

    {{-- Divider --}}
    <div class="flex items-center gap-6 mb-12">
        <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
        <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">All Reviews</span>
        <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
    </div>

    {{-- Review Cards Grid --}}
    <div class="grid md:grid-cols-2 gap-6 mb-12">
        @foreach($reviews->skip(1) as $review)
        <div class="review-card p-8 rounded-2xl" style="background: white; border: 1px solid var(--warm-200);">
            <div class="flex items-start gap-4 mb-4">
                {{-- Avatar with Initial --}}
                <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center font-display font-bold text-lg" style="background: var(--warm-200); color: var(--warm-600);">
                    {{ strtoupper(substr($review->customer_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-lg" style="color: var(--warm-900);">{{ $review->customer_name }}</p>
                    <div class="flex items-center gap-3">
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
            <p class="text-base leading-relaxed" style="color: var(--warm-700);">"{{ $review->comment }}"</p>
            @endif
            @if($review->product)
            <p class="text-sm mt-3 font-medium" style="color: var(--warm-500);">{{ $review->product->name }}</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $reviews->links() }}
    </div>

    @else
    {{-- Empty state --}}
    <div class="text-center py-16">
        <p class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: var(--warm-800);">No reviews yet</p>
        <p class="text-lg" style="color: var(--warm-600);">Be the first to share your experience.</p>
    </div>
    @endif
</div>

{{-- Leave a Review CTA --}}
<section class="relative py-24 overflow-hidden" style="background: var(--warm-900);">
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
