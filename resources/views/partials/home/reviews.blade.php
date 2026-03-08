@php
    $count = $config['count'] ?? 3;
    $title = $config['title'] ?? 'Kind Words';
    $subtitle = $config['subtitle'] ?? 'What our customers say';
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take($count)->get();
@endphp
@if($reviews->count() > 0)
<section class="py-24 px-4" style="background: var(--warm-900);">
    <div class="max-w-6xl mx-auto">
        
        <h2 class="font-display text-3xl md:text-5xl font-semibold text-center mb-16" style="color: var(--warm-100);">{{ $title }}</h2>

        @if($reviews->count() >= 3)
        {{-- Three reviews: large center, smaller flanks --}}
        <div class="grid md:grid-cols-4 gap-6 items-start">
            {{-- Left small --}}
            <div class="md:col-span-1 rounded-2xl p-6 md:mt-12" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(139, 104, 68, 0.15);">
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= $reviews[2]->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="italic text-sm leading-relaxed mb-4" style="color: var(--warm-300);">"{{ $reviews[2]->comment }}"</p>
                <p class="text-sm font-semibold" style="color: var(--warm-400);">{{ $reviews[2]->customer_name }}</p>
            </div>

            {{-- Center large --}}
            <div class="md:col-span-2 rounded-2xl p-10 text-center relative" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(139, 104, 68, 0.25);">
                <span class="font-display font-bold absolute top-4 left-6 leading-none" style="font-size: 4rem; color: var(--warm-500); opacity: 0.15;">&ldquo;</span>
                <div class="flex justify-center mb-6">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= $reviews[0]->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="font-display italic text-xl md:text-2xl leading-relaxed mb-6" style="color: var(--warm-100);">"{{ $reviews[0]->comment }}"</p>
                <p class="font-semibold text-lg" style="color: var(--warm-400);">{{ $reviews[0]->customer_name }}</p>
            </div>

            {{-- Right small --}}
            <div class="md:col-span-1 rounded-2xl p-6 md:mt-12" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(139, 104, 68, 0.15);">
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= $reviews[1]->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="italic text-sm leading-relaxed mb-4" style="color: var(--warm-300);">"{{ $reviews[1]->comment }}"</p>
                <p class="text-sm font-semibold" style="color: var(--warm-400);">{{ $reviews[1]->customer_name }}</p>
            </div>
        </div>
        @else
        {{-- Fewer than 3: simple stack --}}
        <div class="grid md:grid-cols-{{ $reviews->count() }} gap-8">
            @foreach($reviews as $review)
            <div class="rounded-2xl p-8 text-center" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(139, 104, 68, 0.2);">
                <div class="flex justify-center mb-5">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= $review->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="italic leading-relaxed mb-6" style="color: var(--warm-300);">"{{ $review->comment }}"</p>
                <p class="font-semibold" style="color: var(--warm-400);">{{ $review->customer_name }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <div class="text-center mt-12">
            <a href="{{ route('storefront.reviews') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-500);">
                Read All Reviews
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
