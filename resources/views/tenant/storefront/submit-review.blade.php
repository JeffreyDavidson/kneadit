@use(App\Presenters\OrderItemPresenter)
<x-layouts.storefront>
    @if (isset($success) && $success)
        {{-- Success State --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Review submitted"
            image-class="hero-img"
            min-height="80vh"
        >
            <div class="relative z-10 flex min-h-[70vh] items-center justify-center px-4">
                <div class="max-w-md text-center">
                    <div class="hero-fade-1 bg-warm-500/15 border-warm-500 mb-8 inline-flex h-24 w-24 items-center justify-center rounded-full border-2">
                        <x-heroicon-o-check class="text-warm-500 h-12 w-12" stroke-width="2.5" />
                    </div>
                    <h1 class="font-display hero-fade-2 text-warm-100 mb-4 text-4xl font-bold">
                        {{ $content['success_title'] ?? 'Thank You!' }}
                    </h1>
                    <p class="hero-fade-3 text-warm-100 mb-8 text-lg">
                        {{ $content['success_description'] ?? 'Your review has been submitted and will appear once approved. We appreciate your feedback!' }}
                    </p>
                    <x-storefront.button :href="route('storefront.menu')" size="md" class="hero-fade-4">
                        Back to Menu
                    </x-storefront.button>
                </div>
            </div>
        </x-storefront.hero-section>

    @else
        {{-- Photo-Forward Hero --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Share your experience"
            image-class="hero-img"
            min-height="40vh"
        >
            <div class="relative z-10 px-4 py-16 text-center md:py-24">
                <x-storefront.eyebrow class="hero-fade-1 mb-6">
                    {{ $content['hero_eyebrow'] ?? 'We\'d Love to Hear From You' }}</x-storefront.eyebrow>
                <h1 class="font-display hero-fade-2 text-warm-100 mb-4 text-4xl font-bold md:text-6xl">
                    {{ $content['hero_title'] ?? 'How Was Your Order?' }}
                </h1>
                <p class="hero-fade-3 text-warm-100 text-lg">
                    From {{ $settings->store->name }} · Order #{{ $order->order_number }}
                </p>
            </div>
        </x-storefront.hero-section>

        {{-- Review Form on Cream Background --}}
        <section class="bg-warm-100">
            <div class="mx-auto max-w-2xl px-4 py-16 md:py-24">
                {{-- Order Summary --}}
                <div class="border-warm-200 mb-8 rounded-2xl border bg-white p-5">
                    <span class="text-warm-500 mb-3 block text-xs font-medium tracking-wider uppercase">Your Order</span>
                    @foreach ($order->orderItems as $item)
                        <div class="flex justify-between py-1 text-sm">
                            <span class="text-warm-700">{{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}</span>
                            <span class="text-warm-500">@money(OrderItemPresenter::for($item)->totalPrice())</span>
                        </div>
                    @endforeach
                </div>

                <form
                    data-test="review-submission-form"
                    action="{{ route('storefront.storeReview', $order) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-8"
                >
                    @csrf

                    {{-- Star Rating --}}
                    <div class="text-center" x-data="ratingPicker({{ $prefilledRating ?? 0 }}, true)">
                        <label class="text-warm-600 mb-4 block text-xs font-medium tracking-wider uppercase">{{ $content['rating_label'] ?? 'Your Rating' }}</label>
                        <div class="mb-2 flex justify-center gap-3">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    data-test="review-submission-form-rating-{{ $i }}"
                                    x-on:click="set({{ $i }})"
                                    x-on:mouseenter="enter({{ $i }})"
                                    x-on:mouseleave="leave()"
                                    class="transition-all duration-200 focus:outline-none"
                                    x-bind:class="isFilled({{ $i }}) ? 'scale-110' : 'scale-100 opacity-30'"
                                >
                                    <x-heroicon-s-star
                                        class="h-10 w-10"
                                        ::class="isFilled({{ $i }}) ? 'text-warm-500' : 'text-warm-300'"
                                    />
                                </button>
                            @endfor
                            <input type="hidden" name="rating" x-bind:value="rating" />
                        </div>
                        <p class="text-warm-500 text-sm" x-show="rating > 0">
                            <span x-text="{{ json_encode($ratingDescriptions) }}[rating]"></span>
                        </p>
                        @error('rating')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Comment --}}
                    <div>
                        <label
                            for="comment"
                            class="text-warm-600 mb-2 block text-xs font-medium tracking-wider uppercase"
                        >{{ $content['comment_label'] ?? 'Tell Us About Your Experience' }}</label>
                        <textarea
                            data-test="review-submission-form-comment"
                            name="comment"
                            id="comment"
                            rows="5"
                            class="bg-warm-50 border-warm-200 text-warm-800 focus:border-warm-500 w-full rounded-xl border-[1.5px] p-4 text-base transition-colors outline-none"
                            placeholder="{{ $content['comment_placeholder'] ?? 'What did you love? What could we improve?' }}"
                        >{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Photo Upload --}}
                    <div>
                        <label for="photo" class="text-warm-600 mb-2 block text-xs font-medium tracking-wider uppercase"
                            >{{ $content['photo_label'] ?? 'Add a Photo' }}
                            <span class="text-warm-400">(optional)</span></label>
                        <div
                            class="bg-warm-50 border-warm-300 cursor-pointer rounded-xl border-2 border-dashed p-6 text-center transition-all"
                            onclick="document.getElementById('photo').click()"
                        >
                            <x-heroicon-o-photo class="text-warm-400 mx-auto mb-2 h-8 w-8" />
                            <p class="text-warm-500 text-sm">Click to upload a photo</p>
                        </div>
                        <input type="file" name="photo" id="photo" accept="image/*" class="hidden" />
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <x-storefront.button
                        type="submit"
                        size="lg"
                        fullWidth
                        fontDisplay
                        data-test="review-submission-form-submit"
                    >
                        {{ $content['submit_button'] ?? 'Submit Review' }}
                    </x-storefront.button>
                </form>
            </div>
        </section>
    @endif
</x-layouts.storefront>
