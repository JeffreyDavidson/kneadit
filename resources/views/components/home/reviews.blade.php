@if ($reviews->count() > 0)
<section class="relative py-28 px-4 overflow-hidden bg-warm-100">
    <div class="max-w-6xl mx-auto">
        {{-- Section eyebrow --}}
        <div class="text-center mb-6">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">What People Say</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
        </div>

        @if ($featuredReview)
        {{-- Hero testimonial: massive quote --}}
        <div class="text-center mb-20 max-w-4xl mx-auto">
            {{-- Big decorative quote mark --}}
            <div class="font-display font-bold leading-none mb-6" style="font-size: 6rem; color: var(--warm-500); opacity: 0.15; line-height: 0.6;">&ldquo;</div>

            <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-8 text-warm-800 tracking-tight">
                {{ $featuredReview->comment }}
            </blockquote>

            {{-- Stars --}}
            <x-storefront.star-rating :rating="$featuredReview->rating" empty-color="--warm-300" class="justify-center mb-4" />

            <p class="font-semibold text-lg text-warm-700">{{ $featuredReview->customer_name }}</p>
        </div>
        @endif

        @if ($reviews->count() > 1)
        {{-- Secondary reviews: horizontal cards --}}
        <div class="grid md:grid-cols-{{ min($reviews->count() - 1, 3) }} gap-6">
            @foreach ($reviews->skip(1)->take(3) as $review)
            <div class="p-8 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border border-warm-200">
                {{-- Stars --}}
                <x-storefront.star-rating :rating="$review->rating" size="sm" empty-color="--warm-300" class="mb-4" />
                <p class="text-base leading-relaxed mb-6 text-warm-700">"{{ Str::limit($review->comment ?? '', 150) }}"</p>
                <div class="flex items-center gap-3">
                    <x-storefront.avatar-initial :name="$review->customer_name" />
                    <span class="font-semibold text-sm text-warm-800">{{ $review->customer_name }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="text-center mt-12">
            <a href="{{ route('storefront.reviews') }}" class="inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3 text-warm-600">
                Read All Reviews
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
