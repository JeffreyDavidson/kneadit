@extends('layouts.storefront')

@section('content')
@if(isset($success) && $success)
{{-- Success State --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); min-height: 80vh;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 40%, rgba(212,146,12,0.08), transparent 60%);"></div>

    <div class="relative z-10 flex items-center justify-center min-h-[70vh] px-4">
        <div class="text-center max-w-md">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-8" style="background: rgba(212,146,12,0.15); border: 2px solid var(--warm-500);">
                <svg class="w-12 h-12" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="font-display text-4xl font-bold mb-4" style="color: var(--warm-100);">Thank You!</h1>
            <p class="text-lg mb-8" style="color: var(--warm-400);">Your review has been submitted and will appear once approved. We appreciate your feedback!</p>
            <a href="{{ route('storefront.menu') }}" class="inline-block px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                Back to Menu
            </a>
        </div>
    </div>
</section>

@else
{{-- Dark Hero --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); padding-top: 2rem;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 80%, rgba(212,146,12,0.06), transparent 60%);"></div>

    <div class="relative z-10 text-center px-4 py-16 md:py-24">
        <div class="flex items-center justify-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">We'd Love to Hear From You</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4" style="color: var(--warm-100);">
            How Was Your Order?
        </h1>
        <p class="text-lg" style="color: var(--warm-400);">
            From {{ $storeName }} · Order #{{ $order->order_number }}
        </p>
    </div>
</section>

{{-- Review Form --}}
<section style="background: var(--warm-900);">
    <div class="max-w-2xl mx-auto px-4 pb-24">

        {{-- Order Summary --}}
        <div class="rounded-2xl p-5 mb-8" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.2);">
            <span class="block text-xs uppercase tracking-wider font-medium mb-3" style="color: var(--warm-500);">Your Order</span>
            @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm py-1">
                    <span style="color: var(--warm-300);">{{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}</span>
                    <span style="color: var(--warm-500);">${{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('storefront.store-review', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Star Rating --}}
            <div class="text-center" x-data="{ rating: {{ $prefilledRating ?? 0 }}, hover: 0 }">
                <label class="block text-xs uppercase tracking-wider font-medium mb-4" style="color: var(--warm-500);">Your Rating</label>
                <div class="flex gap-3 justify-center mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button"
                            x-on:click="rating = {{ $i }}"
                            x-on:mouseenter="hover = {{ $i }}"
                            x-on:mouseleave="hover = 0"
                            class="transition-all duration-200 focus:outline-none"
                            :class="(hover || rating) >= {{ $i }} ? 'scale-110' : 'scale-100 opacity-30'"
                        >
                            <svg class="w-10 h-10" :style="(hover || rating) >= {{ $i }} ? 'color: var(--warm-500)' : 'color: var(--warm-600)'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                    <input type="hidden" name="rating" x-bind:value="rating">
                </div>
                <p class="text-sm" style="color: var(--warm-600);" x-show="rating > 0">
                    <span x-text="['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'][rating]"></span>
                </p>
                @error('rating')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Comment --}}
            <div>
                <label for="comment" class="block text-xs uppercase tracking-wider font-medium mb-2" style="color: var(--warm-500);">Tell Us About Your Experience</label>
                <textarea name="comment" id="comment" rows="5"
                    class="w-full p-4 rounded-xl text-base"
                    style="background: var(--warm-800); border: 1.5px solid rgba(139,104,68,0.25); color: var(--warm-200); outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='var(--warm-500)'"
                    onblur="this.style.borderColor='rgba(139,104,68,0.25)'"
                    placeholder="What did you love? What could we improve?">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photo Upload --}}
            <div>
                <label for="photo" class="block text-xs uppercase tracking-wider font-medium mb-2" style="color: var(--warm-500);">Add a Photo <span style="color: var(--warm-700);">(optional)</span></label>
                <div class="rounded-xl p-6 text-center cursor-pointer transition-all"
                     style="background: var(--warm-800); border: 2px dashed rgba(139,104,68,0.3);"
                     onclick="document.getElementById('photo').click()">
                    <svg class="w-8 h-8 mx-auto mb-2" style="color: var(--warm-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm" style="color: var(--warm-500);">Click to upload a photo</p>
                </div>
                <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
                @error('photo')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full py-4 rounded-full text-lg font-semibold transition-all duration-300 hover:scale-[1.02] hover:shadow-lg" style="background: var(--warm-500); color: var(--warm-900); font-family: var(--font-display);">
                Submit Review
            </button>
        </form>
    </div>
</section>
@endif
@endsection