
<x-layouts.storefront>
<x-slot:styles>
<link rel="stylesheet" href="{{ asset('css/submit-review.css') }}">
</x-slot:styles>

@if (isset($success) && $success)
{{-- Success State --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Review submitted" image-class="review-hero-img" min-height="80vh">

    <div class="relative z-10 flex items-center justify-center min-h-[70vh] px-4">
        <div class="text-center max-w-md">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-8 review-fade-up" style="background: rgba(212,146,12,0.15); border: 2px solid var(--warm-500); animation-delay: 0.3s;">
                <svg class="w-12 h-12 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="font-display text-4xl font-bold mb-4 review-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">{{ $content['success_title'] ?? 'Thank You!' }}</h1>
            <p class="text-lg mb-8 review-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">{{ $content['success_description'] ?? 'Your review has been submitted and will appear once approved. We appreciate your feedback!' }}</p>
            <a href="{{ route('storefront.menu') }}" class="inline-block px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 review-fade-up" style="background: var(--warm-500); color: var(--warm-900); animation-delay: 0.9s;">
                Back to Menu
            </a>
        </div>
    </div>
</x-storefront.hero-section>

@else
{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Share your experience" image-class="review-hero-img" min-height="40vh">

    <div class="relative z-10 text-center px-4 py-16 md:py-24">
        <x-storefront.eyebrow class="review-fade-up mb-6" style="animation-delay: 0.3s;">{{ $content['hero_eyebrow'] ?? 'We\'d Love to Hear From You' }}</x-storefront.eyebrow>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 review-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">
            {{ $content['hero_title'] ?? 'How Was Your Order?' }}
        </h1>
        <p class="text-lg review-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">
            From {{ $settings->storeName }} · Order #{{ $order->order_number }}
        </p>
    </div>
</x-storefront.hero-section>

{{-- Review Form on Cream Background --}}
<section class="bg-warm-100">
    <div class="max-w-2xl mx-auto px-4 py-16 md:py-24">

        {{-- Order Summary --}}
        <div class="rounded-2xl p-5 mb-8 bg-white border border-warm-200">
            <span class="block text-xs uppercase tracking-wider font-medium mb-3 text-warm-500">Your Order</span>
            @foreach ($order->orderItems as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-warm-700">{{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}</span>
                    <span class="text-warm-500">${{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('storefront.store-review', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Star Rating --}}
            <div class="text-center" x-data="{ rating: {{ $prefilledRating ?? 0 }}, hover: 0 }">
                <label class="block text-xs uppercase tracking-wider font-medium mb-4 text-warm-600">{{ $content['rating_label'] ?? 'Your Rating' }}</label>
                <div class="flex gap-3 justify-center mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button"
                            x-on:click="rating = {{ $i }}"
                            x-on:mouseenter="hover = {{ $i }}"
                            x-on:mouseleave="hover = 0"
                            class="transition-all duration-200 focus:outline-none"
                            :class="(hover || rating) >= {{ $i }} ? 'scale-110' : 'scale-100 opacity-30'"
                        >
                            <svg class="w-10 h-10" :style="(hover || rating) >= {{ $i }} ? 'color: var(--warm-500)' : 'color: var(--warm-300)'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                    <input type="hidden" name="rating" x-bind:value="rating">
                </div>
                <p class="text-sm text-warm-500" x-show="rating > 0">
                    <span x-text="{{ json_encode($ratingDescriptions) }}[rating]"></span>
                </p>
                @error('rating')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Comment --}}
            <div>
                <label for="comment" class="block text-xs uppercase tracking-wider font-medium mb-2 text-warm-600">{{ $content['comment_label'] ?? 'Tell Us About Your Experience' }}</label>
                <textarea name="comment" id="comment" rows="5"
                    class="w-full p-4 rounded-xl text-base"
                    style="background: var(--warm-50); border: 1.5px solid var(--warm-200); color: var(--warm-800); outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='var(--warm-500)'"
                    onblur="this.style.borderColor='var(--warm-200)'"
                    placeholder="{{ $content['comment_placeholder'] ?? 'What did you love? What could we improve?' }}">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photo Upload --}}
            <div>
                <label for="photo" class="block text-xs uppercase tracking-wider font-medium mb-2 text-warm-600">{{ $content['photo_label'] ?? 'Add a Photo' }} <span class="text-warm-400">(optional)</span></label>
                <div class="rounded-xl p-6 text-center cursor-pointer transition-all"
                     style="background: var(--warm-50); border: 2px dashed var(--warm-300);"
                     onclick="document.getElementById('photo').click()">
                    <svg class="w-8 h-8 mx-auto mb-2 text-warm-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-warm-500">Click to upload a photo</p>
                </div>
                <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
                @error('photo')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full py-4 rounded-full text-lg font-semibold transition-all duration-300 hover:scale-[1.02] hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900); font-family: var(--font-display);">
                {{ $content['submit_button'] ?? 'Submit Review' }}
            </button>
        </form>
    </div>
</section>
@endif

</x-layouts.storefront>
