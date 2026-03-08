@extends('layouts.storefront')

@section('content')

{{-- Hero --}}
<div class="py-20 md:py-28 text-center" style="background: var(--warm-100);">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight mb-8" style="color: var(--warm-900);">
            Kind Words
        </h1>

        @if($totalReviews > 0)
        <div class="inline-flex flex-col items-center">
            <div class="flex gap-1 mb-3">
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-7 h-7 md:w-8 md:h-8" style="color: {{ $i <= round($avgRating) ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            <p class="font-display text-3xl md:text-4xl font-bold" style="color: var(--warm-900);">{{ number_format($avgRating, 1) }}</p>
            <p class="text-sm mt-1" style="color: var(--warm-600);">from {{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
        </div>
        @endif
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-16 md:py-24">
    @if($reviews->count() > 0)

    {{-- Featured review (first/most recent) --}}
    @php $featured = $reviews->first(); @endphp
    <div class="mb-20 text-center max-w-3xl mx-auto">
        @if($featured->comment)
        <blockquote class="font-display text-2xl md:text-3xl lg:text-4xl font-medium leading-relaxed mb-8" style="color: var(--warm-800);">
            "{{ $featured->comment }}"
        </blockquote>
        @endif
        <div>
            <p class="font-semibold" style="color: var(--warm-900);">{{ $featured->customer_name }}</p>
            <div class="flex justify-center gap-0.5 mt-2">
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-4 h-4" style="color: {{ $i <= $featured->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            @if($featured->product)
            <p class="text-sm mt-2" style="color: var(--warm-500);">on {{ $featured->product->name }}</p>
            @endif
        </div>
    </div>

    {{-- Divider --}}
    <div class="flex items-center gap-6 mb-12">
        <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
        <span class="text-sm tracking-widest uppercase font-medium" style="color: var(--warm-500);">All Reviews</span>
        <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
    </div>

    {{-- Remaining reviews as clean list --}}
    <div class="space-y-0">
        @foreach($reviews->skip(1) as $review)
        <div class="py-8 {{ !$loop->first ? 'border-t' : '' }}" style="border-color: var(--warm-200);">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                <div>
                    <p class="font-semibold text-lg" style="color: var(--warm-900);">{{ $review->customer_name }}</p>
                    @if($review->product)
                    <p class="text-sm" style="color: var(--warm-500);">{{ $review->product->name }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4" style="color: {{ $i <= $review->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <span class="text-sm" style="color: var(--warm-400);">{{ $review->created_at->format('M j, Y') }}</span>
                </div>
            </div>
            @if($review->comment)
            <p class="text-lg leading-relaxed" style="color: var(--warm-700);">"{{ $review->comment }}"</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mt-12">
        {{ $reviews->links() }}
    </div>

    @else
    {{-- Empty state --}}
    <div class="text-center py-16">
        <p class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: var(--warm-800);">No reviews yet</p>
        <p class="text-lg" style="color: var(--warm-600);">Be the first to share your experience.</p>
    </div>
    @endif

    {{-- Leave a Review CTA --}}
    <div class="mt-20 text-center">
        <div class="py-16 px-8 rounded-2xl" style="background: var(--warm-100);">
            <p class="font-script text-xl mb-3" style="color: var(--warm-500);">Enjoyed something?</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: var(--warm-900);">
                We'd love to hear about it.
            </h2>
            <p class="text-lg mb-8 max-w-lg mx-auto" style="color: var(--warm-600);">
                Your feedback helps us bake better and helps others discover their next favorite treat.
            </p>
            <a href="{{ route('order.track') }}" class="btn-primary text-lg px-8 py-4 inline-block">
                Leave a Review
            </a>
        </div>
    </div>
</div>
@endsection