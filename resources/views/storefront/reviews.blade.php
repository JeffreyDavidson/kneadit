<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/reviews.css') }}">

{{-- Photo-Forward Hero with Dark Overlay --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" :image-alt="$settings->storeName . ' Reviews'" image-class="review-hero-img" min-height="60vh" gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[60vh]">
        <x-storefront.eyebrow class="review-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'What People Say' }}</x-storefront.eyebrow>
        <h1 class="review-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
            {{ $content['hero_title'] ?? 'Kind Words' }}
        </h1>
        @if ($vm->totalReviews > 0)
        <p class="review-fade-2 font-script text-2xl md:text-3xl text-warm-400">{{ $vm->totalReviews }} {{ Str::plural('review', $vm->totalReviews) }} from happy customers</p>
        @endif
    </div>
</x-storefront.hero-section>

{{-- Stats Strip --}}
@if ($vm->totalReviews > 0)
<section class="bg-warm-800">
    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->formattedAvgRating }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Average Rating</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->totalReviews }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Total Reviews</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->fiveStarPct }}%</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">5-Star Reviews</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span class="block font-display text-3xl md:text-4xl font-bold text-warm-400">
                    <x-storefront.star-rating :rating="round($vm->avgRating)" size="lg" empty-color="--warm-300" />
                </span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Overall</span>
            </div>
        </div>
    </div>
</section>
@endif

@if ($reviews->count() > 0)

{{-- Featured Review: Massive Pull-Quote --}}
<section class="relative py-24 md:py-32 overflow-hidden bg-warm-100">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="font-display font-bold leading-none mb-6" style="font-size: 8rem; color: var(--warm-500); opacity: 0.12; line-height: 0.5;">&ldquo;</div>
        @if ($featured->comment)
        <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-10 text-warm-800 tracking-tight">
            {{ $featured->comment }}
        </blockquote>
        @endif
        <x-storefront.star-rating :rating="$featured->rating" empty-color="--warm-300" class="justify-center mb-4" />
        <p class="font-semibold text-lg text-warm-700">{{ $featured->customer_name }}</p>
        @if ($featured->product)
        <p class="text-sm mt-1 text-warm-500">on {{ $featured->product->name }}</p>
        @endif
    </div>
</section>

{{-- Rating Distribution --}}
<x-storefront.dark-section radial-position="30% 50%">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">{{ $content['rating_eyebrow'] ?? 'Rating Breakdown' }}</x-storefront.eyebrow>
        </div>

        <div class="space-y-4">
            @foreach ($vm->ratingBreakdown as $star => $data)
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 flex-shrink-0" style="width: 80px;">
                    <span class="font-display font-semibold text-warm-300">{{ $star }}</span>
                    <svg class="w-4 h-4 text-warm-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div class="flex-1 rating-bar-track bg-warm-800">
                    <div class="rating-bar-fill" style="width: {{ $data['pct'] }}%; background: var(--warm-500);"></div>
                </div>
                <span class="text-sm font-medium flex-shrink-0" style="color: var(--warm-400); width: 40px; text-align: right;">{{ $data['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>

{{-- All Reviews Grid --}}
<section id="reviews" class="relative py-24 md:py-28 bg-warm-100">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-6 mb-16">
            <div class="flex-1 h-px bg-warm-300"></div>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">{{ $content['all_reviews_label'] ?? 'All Reviews' }}</span>
            <div class="flex-1 h-px bg-warm-300"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mb-16">
            @foreach ($reviews->skip(1) as $review)
            <div class="review-card p-8 rounded-2xl bg-white border border-warm-200">
                <div class="flex items-start gap-4 mb-5">
                    <x-storefront.avatar-initial :name="$review->customer_name" size="lg" class="flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-lg text-warm-900">{{ $review->customer_name }}</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <x-storefront.star-rating :rating="$review->rating" size="sm" empty-color="--warm-300" />
                            <span class="text-xs text-warm-400">{{ $review->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
                @if ($review->comment)
                <p class="text-base leading-relaxed mb-4 text-warm-700">&ldquo;{{ $review->comment }}&rdquo;</p>
                @endif
                @if ($review->product)
                <p class="text-sm font-medium text-warm-500">{{ $review->product->name }}</p>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $reviews->fragment('reviews')->links() }}
        </div>
    </div>
</section>

@else
{{-- Empty state --}}
<section class="relative py-24 bg-warm-100">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="font-display font-bold leading-none mb-4" style="font-size: 5rem; color: var(--warm-300); opacity: 0.3; line-height: 0.6;">&ldquo;</div>
        <p class="font-display text-3xl md:text-4xl font-bold mb-4 text-warm-800">{{ $content['empty_heading'] ?? 'No reviews yet' }}</p>
        <p class="text-lg leading-relaxed text-warm-600">{{ $content['empty_description'] ?? 'Be the first to share your experience.' }}</p>
    </div>
</section>
@endif

{{-- Leave a Review CTA --}}
<x-storefront.cta-section
    :script-text="$content['cta_script'] ?? 'Enjoyed something?'"
    :heading="$content['cta_heading'] ?? 'We\'d love to hear about it.'"
    :description="$content['cta_description'] ?? 'Your feedback helps us bake better and helps others discover their next favorite treat.'"
    :button-text="$content['cta_button'] ?? 'Leave a Review'"
    :button-route="route('order.track')"
/>
</x-layouts.storefront>
