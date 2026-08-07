<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/reviews.css') }}" />

    {{-- Photo-Forward Hero with Dark Overlay --}}
    <x-storefront.hero-section
        :image="$vm->settings->heroImageUrl()"
        :image-alt="$vm->settings->store->name . ' Reviews'"
        image-class="hero-img"
        min-height="60vh"
        gradient="linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.6) 50%, rgba(28,20,16,0.95) 100%)"
    >
        <div class="relative z-10 flex min-h-[60vh] flex-col items-center justify-end px-4 pb-20 text-center">
            <x-storefront.eyebrow class="hero-fade-1 mb-6">
                {{ $vm->content['hero_eyebrow'] ?? 'What People Say' }}</x-storefront.eyebrow>
            <h1 class="hero-fade-1 font-display text-warm-100 mb-6 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                {{ $vm->content['hero_title'] ?? 'Kind Words' }}
            </h1>
            @if ($vm->totalReviews > 0)
                <p class="hero-fade-2 font-script text-warm-400 text-2xl md:text-3xl">
                    {{ $vm->totalReviews }} {{ Str::plural('review', $vm->totalReviews) }} from happy customers
                </p>
            @endif
        </div>
    </x-storefront.hero-section>

    {{-- Stats Strip --}}
    @if ($vm->totalReviews > 0)
        <section class="bg-warm-800">
            <div class="mx-auto max-w-5xl px-4 py-12">
                <div
                    class="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8"
                    x-data="countUpStats([
                { ref: 'avg', value: {{ $vm->avgRating }} },
                { ref: 'total', value: {{ $vm->totalReviews }} },
                { ref: 'pct', value: {{ $vm->fiveStarPct }}, suffix: '%' },
             ])"
                    x-intersect.once="$nextTick(() => runStats())"
                >
                    <x-storefront.stat-display
                        wrapper-class="text-center transition-all duration-300 hover:-translate-y-1"
                        x-ref="avg"
                        label="Average Rating"
                    >
                        @number($vm->avgRating, 1)
                    </x-storefront.stat-display>
                    <x-storefront.stat-display
                        wrapper-class="text-center transition-all duration-300 hover:-translate-y-1"
                        x-ref="total"
                        :value="$vm->totalReviews"
                        label="Total Reviews"
                    />
                    <x-storefront.stat-display
                        wrapper-class="text-center transition-all duration-300 hover:-translate-y-1"
                        x-ref="pct"
                        :value="$vm->fiveStarPct . '%'"
                        label="5-Star Reviews"
                    />
                    <x-storefront.stat-display
                        wrapper-class="text-center transition-all duration-300 hover:-translate-y-1"
                        label="Overall"
                    >
                        <x-storefront.star-rating :rating="round($vm->avgRating)" size="lg" empty-color="--warm-300" />
                    </x-storefront.stat-display>
                </div>
            </div>
        </section>
    @endif

    @if ($vm->reviews->count() > 0)
        {{-- Featured Review: Massive Pull-Quote --}}
        <section class="bg-warm-100 relative overflow-hidden py-24 md:py-32">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <x-storefront.pull-quote-mark size="lg" tone="warm-faint" class="mb-6" />
                @if ($vm->featured()->comment)
                    <blockquote class="font-display text-warm-800 mb-10 text-2xl leading-snug font-medium tracking-tight md:text-4xl lg:text-5xl">
                        {{ $vm->featured()->comment }}
                    </blockquote>
                @endif
                <x-storefront.star-rating
                    :rating="$vm->featured()->rating"
                    empty-color="--warm-300"
                    class="mb-4 justify-center"
                />
                <p class="text-warm-700 text-lg font-semibold">{{ $vm->featured()->customer_name }}</p>
                @if ($vm->featured()->product)
                    <p class="text-warm-500 mt-1 text-sm">on {{ $vm->featured()->product->name }}</p>
                @endif
            </div>
        </section>

        {{-- Rating Distribution --}}
        <x-storefront.dark-section radial-position="30% 50%">
            <div class="mx-auto max-w-3xl px-4">
                <div class="mb-12 text-center">
                    <x-storefront.eyebrow line-opacity="0.5" class="mb-4">
                        {{ $vm->content['rating_eyebrow'] ?? 'Rating Breakdown' }}</x-storefront.eyebrow>
                </div>

                <div class="space-y-4" x-data="ratingBars" x-intersect.once="animate()">
                    @foreach ($vm->ratingBreakdown as $star => $data)
                        <div class="flex items-center gap-4">
                            <div class="flex w-20 flex-shrink-0 items-center gap-1">
                                <span class="font-display text-warm-300 font-semibold">{{ $star }}</span>
                                <x-heroicon-s-star class="text-warm-500 h-4 w-4" />
                            </div>
                            <div class="rating-bar-track bg-warm-800 flex-1">
                                <div
                                    class="rating-bar-fill bg-warm-500"
                                    data-pct="{{ $data['pct'] }}"
                                    style="width: {{ $data['pct'] }}%;"
                                ></div>
                            </div>
                            <span class="text-warm-400 w-10 flex-shrink-0 text-right text-sm font-medium">{{ $data['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-storefront.dark-section>

        {{-- All Reviews Grid --}}
        <section id="reviews" class="bg-warm-100 relative py-24 md:py-28">
            <div class="mx-auto max-w-6xl px-4">
                <x-storefront.section-divider tone="light" class="mb-16">
                    {{ $vm->content['all_reviews_label'] ?? 'All Reviews' }}
                </x-storefront.section-divider>

                <div class="mb-16 grid gap-8 md:grid-cols-2">
                    @foreach ($vm->reviews->skip(1) as $review)
                        <div
                            class="review-card border-warm-200 rounded-2xl border bg-white p-8 shadow-sm"
                            x-data="reviewCardFadeIn({{ $loop->index }})"
                            x-intersect.once="show()"
                        >
                            <div class="mb-5 flex items-start gap-4">
                                <x-storefront.avatar-initial
                                    :name="$review->customer_name"
                                    size="lg"
                                    class="flex-shrink-0"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="text-warm-900 text-lg font-semibold">{{ $review->customer_name }}</p>
                                    <div class="mt-0.5 flex items-center gap-3">
                                        <x-storefront.star-rating
                                            :rating="$review->rating"
                                            size="sm"
                                            empty-color="--warm-300"
                                        />
                                        <span class="text-warm-400 text-xs">{{ $review->created_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            @if ($review->comment)
                                <p class="text-warm-700 mb-4 text-base leading-relaxed">
                                    &ldquo;{{ $review->comment }}&rdquo;
                                </p>
                            @endif
                            @if ($review->product)
                                <p class="text-warm-500 text-sm font-medium">{{ $review->product->name }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="flex justify-center">{{ $vm->reviews->fragment('reviews')->links() }}</div>
            </div>
        </section>

    @else
        {{-- Empty state --}}
        <section class="bg-warm-100 relative py-24">
            <div class="mx-auto max-w-md px-4 text-center">
                <x-storefront.pull-quote-mark size="md" tone="warm-muted" class="mb-4" />
                <p class="font-display text-warm-800 mb-4 text-3xl font-bold md:text-4xl">
                    {{ $vm->content['empty_heading'] ?? 'No reviews yet' }}
                </p>
                <p class="text-warm-600 text-lg leading-relaxed">
                    {{ $vm->content['empty_description'] ?? 'Be the first to share your experience.' }}
                </p>
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
