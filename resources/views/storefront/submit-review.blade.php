@use(App\Presenters\OrderItemPresenter)
<x-layouts.storefront>

@if (isset($success) && $success)
{{-- Success State --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Review submitted" image-class="hero-img" min-height="80vh">

 <div class="relative z-10 flex items-center justify-center min-h-[70vh] px-4">
 <div class="text-center max-w-md">
 <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-8 hero-fade-1 bg-warm-500/15 border-2 border-warm-500">
 <x-heroicon-o-check class="w-12 h-12 text-warm-500" stroke-width="2.5" />
 </div>
 <h1 class="font-display text-4xl font-bold mb-4 hero-fade-2 text-warm-100">{{ $content['success_title'] ?? 'Thank You!' }}</h1>
 <p class="text-lg mb-8 hero-fade-3 text-warm-100">{{ $content['success_description'] ?? 'Your review has been submitted and will appear once approved. We appreciate your feedback!' }}</p>
 <x-storefront.button :href="route('storefront.menu')" size="md" class="hero-fade-4">
 Back to Menu
 </x-storefront.button>
 </div>
 </div>
</x-storefront.hero-section>

@else
{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Share your experience" image-class="hero-img" min-height="40vh">

 <div class="relative z-10 text-center px-4 py-16 md:py-24">
 <x-storefront.eyebrow class="hero-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'We\'d Love to Hear From You' }}</x-storefront.eyebrow>
 <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 hero-fade-2 text-warm-100">
 {{ $content['hero_title'] ?? 'How Was Your Order?' }}
 </h1>
 <p class="text-lg hero-fade-3 text-warm-100">
 From {{ $settings->store->name }} · Order #{{ $order->order_number }}
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
 <span class="text-warm-500">@money(OrderItemPresenter::for($item)->totalPrice())</span>
 </div>
 @endforeach
 </div>

 <form data-test="review-submission-form" action="{{ route('storefront.storeReview', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
 @csrf

 {{-- Star Rating --}}
 <div class="text-center" x-data="ratingPicker({{ $prefilledRating ?? 0 }}, true)">
 <label class="block text-xs uppercase tracking-wider font-medium mb-4 text-warm-600">{{ $content['rating_label'] ?? 'Your Rating' }}</label>
 <div class="flex gap-3 justify-center mb-2">
 @for ($i = 1; $i <= 5; $i++)
 <button type="button"
 data-test="review-submission-form-rating-{{ $i }}"
 x-on:click="set({{ $i }})"
 x-on:mouseenter="enter({{ $i }})"
 x-on:mouseleave="leave()"
 class="transition-all duration-200 focus:outline-none"
 x-bind:class="isFilled({{ $i }}) ? 'scale-110' : 'scale-100 opacity-30'"
 >
 <x-heroicon-s-star class="w-10 h-10" ::class="isFilled({{ $i }}) ? 'text-warm-500' : 'text-warm-300'" />
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
 <textarea data-test="review-submission-form-comment" name="comment" id="comment" rows="5"
 class="w-full p-4 rounded-xl text-base bg-warm-50 border-[1.5px] border-warm-200 text-warm-800 outline-none transition-colors focus:border-warm-500"
 placeholder="{{ $content['comment_placeholder'] ?? 'What did you love? What could we improve?' }}">{{ old('comment') }}</textarea>
 @error('comment')
 <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 {{-- Photo Upload --}}
 <div>
 <label for="photo" class="block text-xs uppercase tracking-wider font-medium mb-2 text-warm-600">{{ $content['photo_label'] ?? 'Add a Photo' }} <span class="text-warm-400">(optional)</span></label>
 <div class="rounded-xl p-6 text-center cursor-pointer transition-all bg-warm-50 border-2 border-dashed border-warm-300"
 onclick="document.getElementById('photo').click()">
 <x-heroicon-o-photo class="w-8 h-8 mx-auto mb-2 text-warm-400" />
 <p class="text-sm text-warm-500">Click to upload a photo</p>
 </div>
 <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
 @error('photo')
 <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 {{-- Submit --}}
 <x-storefront.button type="submit" size="lg" fullWidth fontDisplay data-test="review-submission-form-submit">
 {{ $content['submit_button'] ?? 'Submit Review' }}
 </x-storefront.button>
 </form>
 </div>
</section>
@endif

</x-layouts.storefront>
