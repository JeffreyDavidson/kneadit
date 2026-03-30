@if ($reviews->count() > 0)
<section class="relative py-28 px-4 overflow-hidden" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto">
        {{-- Section eyebrow --}}
        <div class="text-center mb-6">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">What People Say</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            </div>
        </div>

        @if ($featuredReview)
        {{-- Hero testimonial: massive quote --}}
        <div class="text-center mb-20 max-w-4xl mx-auto">
            {{-- Big decorative quote mark --}}
            <div class="font-display font-bold leading-none mb-6" style="font-size: 6rem; color: var(--warm-500); opacity: 0.15; line-height: 0.6;">&ldquo;</div>

            <blockquote class="font-display text-2xl md:text-4xl lg:text-5xl font-medium leading-snug mb-8" style="color: var(--warm-800); letter-spacing: -0.01em;">
                {{ $featuredReview->comment }}
            </blockquote>

            {{-- Stars --}}
            <div class="flex justify-center gap-1 mb-4">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5" style="color: {{ $i <= $featuredReview->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
            </div>

            <p class="font-semibold text-lg" style="color: var(--warm-700);">{{ $featuredReview->customer_name }}</p>
        </div>
        @endif

        @if ($reviews->count() > 1)
        {{-- Secondary reviews: horizontal cards --}}
        <div class="grid md:grid-cols-{{ min($reviews->count() - 1, 3) }} gap-6">
            @foreach ($reviews->skip(1)->take(3) as $review)
            <div class="p-8 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg" style="background: white; border: 1px solid var(--warm-200);">
                {{-- Stars --}}
                <div class="flex gap-0.5 mb-4">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4" style="color: {{ $i <= $review->rating ? 'var(--warm-500)' : 'var(--warm-300)' }};" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-base leading-relaxed mb-6" style="color: var(--warm-700);">"{{ Str::limit($review->comment ?? '', 150) }}"</p>
                <div class="flex items-center gap-3">
                    {{-- Avatar circle with initial --}}
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-display font-bold text-sm" style="background: var(--warm-200); color: var(--warm-600);">
                        {{ strtoupper(substr($review->customer_name, 0, 1)) }}
                    </div>
                    <span class="font-semibold text-sm" style="color: var(--warm-800);">{{ $review->customer_name }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="text-center mt-12">
            <a href="{{ route('storefront.reviews') }}" class="inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3" style="color: var(--warm-600);">
                Read All Reviews
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
