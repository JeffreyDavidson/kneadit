@php
    $count = $config['count'] ?? 3;
    $title = $config['title'] ?? 'Kind Words';
    $subtitle = $config['subtitle'] ?? 'What our customers say';
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take($count)->get();
@endphp
@if($reviews->count() > 0)
<section class="py-20 px-4" style="background: var(--warm-900);">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">{{ $subtitle }}</p>
            <h2 class="font-display text-3xl md:text-5xl font-semibold" style="color: var(--warm-100);">{{ $title }}</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($reviews as $review)
            <div class="rounded-2xl p-8 text-center" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(139, 104, 68, 0.2);">
                <div class="flex justify-center mb-5">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= $review->rating ? '' : 'opacity-20' }}" style="color: var(--warm-500);" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="italic leading-relaxed mb-6" style="color: var(--warm-300);">
                    "{{ $review->comment }}"
                </p>
                <p class="font-semibold" style="color: var(--warm-400);">{{ $review->customer_name }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.reviews') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-500);">
                Read All Reviews →
            </a>
        </div>
    </div>
</section>
@endif
