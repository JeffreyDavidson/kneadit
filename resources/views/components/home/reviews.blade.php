@if ($reviews->count() > 0)
    <section class="bg-warm-100 relative overflow-hidden px-4 py-28">
        <div class="mx-auto max-w-6xl">
            <div class="mb-6 text-center">
                <x-storefront.eyebrow line-opacity="0.5" class="mb-4">What People Say</x-storefront.eyebrow>
            </div>

            @if ($featuredReview)
                {{-- Hero testimonial: massive quote --}}
                <div class="mx-auto mb-20 max-w-4xl text-center">
                    {{-- Big decorative quote mark --}}
                    <div
                        class="font-display mb-6 leading-none font-bold"
                        style="font-size: 6rem; color: var(--warm-500); opacity: 0.15; line-height: 0.6"
                    >
                        &ldquo;
                    </div>

                    <blockquote class="font-display text-warm-800 mb-8 text-2xl leading-snug font-medium tracking-tight md:text-4xl lg:text-5xl">
                        {{ $featuredReview->comment }}
                    </blockquote>

                    {{-- Stars --}}
                    <x-storefront.star-rating
                        :rating="$featuredReview->rating"
                        empty-color="--warm-300"
                        class="mb-4 justify-center"
                    />

                    <p class="text-warm-700 text-lg font-semibold">{{ $featuredReview->customer_name }}</p>
                </div>
            @endif

            @if ($reviews->count() > 1)
                {{-- Secondary reviews: horizontal cards --}}
                <div class="grid md:grid-cols-{{ min($reviews->count() - 1, 3) }} gap-6">
                    @foreach ($reviews->skip(1)->take(3) as $review)
                        <div class="border-warm-200 rounded-2xl border bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            {{-- Stars --}}
                            <x-storefront.star-rating
                                :rating="$review->rating"
                                size="sm"
                                empty-color="--warm-300"
                                class="mb-4"
                            />
                            <p class="text-warm-700 mb-6 text-base leading-relaxed">
                                "{{ Str::limit($review->comment ?? '', 150) }}"
                            </p>
                            <div class="flex items-center gap-3">
                                <x-storefront.avatar-initial :name="$review->customer_name" />
                                <span class="text-warm-800 text-sm font-semibold">{{ $review->customer_name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-12 text-center">
                <a
                    href="{{ route('storefront.reviews') }}"
                    class="text-warm-600 inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3"
                >
                    Read All Reviews
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>
@endif
