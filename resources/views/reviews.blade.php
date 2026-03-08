@extends('layouts.storefront')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-16">
        <p class="font-script text-xl mb-2" style="color: var(--warm-500);">What our customers say</p>
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">
            Customer Reviews
        </h1>
    </div>

    <!-- Average Rating Summary -->
    @if($totalReviews > 0)
    <div class="card p-8 text-center mb-12 max-w-lg mx-auto">
        <div class="flex justify-center mb-3">
            @for($i = 1; $i <= 5; $i++)
            <svg class="w-8 h-8 {{ $i <= round($avgRating) ? '' : 'opacity-25' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            @endfor
        </div>
        <p class="text-2xl font-bold mb-1" style="color: var(--warm-900);">{{ number_format($avgRating, 1) }} out of 5</p>
        <p style="color: var(--warm-700);">Based on {{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
    </div>
    @endif

    <!-- Reviews Grid -->
    @if($reviews->count() > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        @foreach($reviews as $review)
        <div class="card p-8">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-semibold text-lg" style="color: var(--warm-900);">{{ $review->customer_name }}</h3>
                    @if($review->product)
                    <p class="text-sm" style="color: var(--warm-600);">{{ $review->product->name }}</p>
                    @endif
                </div>
                <span class="text-sm" style="color: var(--warm-500);">{{ $review->created_at->format('M j, Y') }}</span>
            </div>

            <div class="flex mb-4">
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-5 h-5 {{ $i <= $review->rating ? '' : 'opacity-25' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>

            @if($review->comment)
            <p class="italic leading-relaxed" style="color: var(--warm-700);">"{{ $review->comment }}"</p>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $reviews->links() }}
    </div>
    @else
    <div class="text-center py-16">
        <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: var(--warm-200);">
            <svg class="w-10 h-10" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <h2 class="font-display text-2xl font-semibold mb-3" style="color: var(--warm-900);">No Reviews Yet</h2>
        <p class="text-lg" style="color: var(--warm-700);">Be the first to share your experience with us!</p>
    </div>
    @endif
</div>
@endsection
