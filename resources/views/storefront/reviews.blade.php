<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/reviews.css') }}">

{{-- Photo-Forward Hero with Dark Overlay --}}
<x-storefront.hero-section :image="$vm->settings->heroImageUrl()" :image-alt="$vm->settings->store->name . ' Reviews'" image-class="review-hero-img" min-height="60vh" gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[60vh]">
        <x-storefront.eyebrow class="review-fade-1 mb-6">{{ $vm->content['hero_eyebrow'] ?? 'What People Say' }}</x-storefront.eyebrow>
        <h1 class="review-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
            {{ $vm->content['hero_title'] ?? 'Kind Words' }}
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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8"
             x-data="{ reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches, countUp(el, target, suffix = '') { if (this.reduced) { el.textContent = (Number.isInteger(target) ? target : target.toFixed(1)) + suffix; return; } let current = 0; const step = target / 30; const interval = setInterval(() => { current = Math.min(current + step, target); el.textContent = (Number.isInteger(target) ? Math.round(current) : current.toFixed(1)) + suffix; if (current >= target) clearInterval(interval); }, 30); } }"
             x-intersect.once="$nextTick(() => { countUp($refs.avg, {{ $vm->avgRating }}); countUp($refs.total, {{ $vm->totalReviews }}); countUp($refs.pct, {{ $vm->fiveStarPct }}, '%'); })">
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span x-ref="avg" class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->formattedAvgRating }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Average Rating</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span x-ref="total" class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->totalReviews }}</span>
                <span class="text-xs uppercase tracking-[0.2em] mt-1 block text-warm-600">Total Reviews</span>
            </div>
            <div class="text-center transition-all duration-300 hover:-translate-y-1">
                <span x-ref="pct" class="block font-display text-3xl md:text-4xl font-bold text-warm-400">{{ $vm->fiveStarPct }}%</span>
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

@if ($vm->reviews->count() > 0)

{{-- Featured Review: Massive Pull-Quote --}}
<section class="relative py-24 md:py-32 overflow-hidden bg-warm-100">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <x-storefront.pull-quote-mark size="lg" tone="warm-faint" class="mb-6" />
        @if ($vm->featured()->comment)
        <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-10 text-warm-800 tracking-tight">
            {{ $vm->featured()->comment }}
        </blockquote>
        @endif
        <x-storefront.star-rating :rating="$vm->featured()->rating" empty-color="--warm-300" class="justify-center mb-4" />
        <p class="font-semibold text-lg text-warm-700">{{ $vm->featured()->customer_name }}</p>
        @if ($vm->featured()->product)
        <p class="text-sm mt-1 text-warm-500">on {{ $vm->featured()->product->name }}</p>
        @endif
    </div>
</section>

{{-- Rating Distribution --}}
<x-storefront.dark-section radial-position="30% 50%">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">{{ $vm->content['rating_eyebrow'] ?? 'Rating Breakdown' }}</x-storefront.eyebrow>
        </div>

        <div class="space-y-4"
             x-data="{ shown: false, reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches }"
             x-init="if (!reduced) { $el.querySelectorAll('.rating-bar-fill').forEach(b => b.style.width = '0%'); }"
             x-intersect.once="shown = true; if (!reduced) { $el.querySelectorAll('.rating-bar-fill').forEach(b => b.style.width = b.dataset.pct + '%'); }">
            @foreach ($vm->ratingBreakdown as $star => $data)
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 flex-shrink-0 w-20">
                    <span class="font-display font-semibold text-warm-300">{{ $star }}</span>
                    <x-heroicon-s-star class="w-4 h-4 text-warm-500" />
                </div>
                <div class="flex-1 rating-bar-track bg-warm-800">
                    <div class="rating-bar-fill bg-warm-500" data-pct="{{ $data['pct'] }}" style="width: {{ $data['pct'] }}%;"></div>
                </div>
                <span class="text-sm font-medium flex-shrink-0 text-right text-warm-400 w-10">{{ $data['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>

{{-- All Reviews Grid --}}
<section id="reviews" class="relative py-24 md:py-28 bg-warm-100">
    <div class="max-w-6xl mx-auto px-4">
        <x-storefront.section-divider tone="light" class="mb-16">
            {{ $vm->content['all_reviews_label'] ?? 'All Reviews' }}
        </x-storefront.section-divider>

        <div class="grid md:grid-cols-2 gap-8 mb-16">
            @foreach ($vm->reviews->skip(1) as $review)
            <div class="review-card p-8 rounded-2xl bg-white border border-warm-200 shadow-sm"
                 x-data="{ reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches }"
                 x-init="if (!reduced) { $el.style.opacity = '0'; $el.style.transform = 'translateY(1rem)'; $el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out'; }"
                 x-intersect.once="if (!reduced) { $el.style.transitionDelay = '{{ $loop->index * 75 }}ms'; $el.style.opacity = '1'; $el.style.transform = 'translateY(0)'; }">
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
            {{ $vm->reviews->fragment('reviews')->links() }}
        </div>
    </div>
</section>

@else
{{-- Empty state --}}
<section class="relative py-24 bg-warm-100">
    <div class="text-center max-w-md mx-auto px-4">
        <x-storefront.pull-quote-mark size="md" tone="warm-muted" class="mb-4" />
        <p class="font-display text-3xl md:text-4xl font-bold mb-4 text-warm-800">{{ $vm->content['empty_heading'] ?? 'No reviews yet' }}</p>
        <p class="text-lg leading-relaxed text-warm-600">{{ $vm->content['empty_description'] ?? 'Be the first to share your experience.' }}</p>
    </div>
</section>
@endif

{{-- Leave a Review CTA --}}
<x-storefront.cta-section
    :script-text="$vm->content['cta_script'] ?? 'Enjoyed something?'"
    :heading="$vm->content['cta_heading'] ?? 'We\'d love to hear about it.'"
    :description="$vm->content['cta_description'] ?? 'Your feedback helps us bake better and helps others discover their next favorite treat.'"
    :button-text="$vm->content['cta_button'] ?? 'Leave a Review'"
    :button-route="route('order.track')"
/>
</x-layouts.storefront>
