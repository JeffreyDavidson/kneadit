@extends('layouts.storefront')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    @if(isset($success) && $success)
        {{-- Success State --}}
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: #d1fae5;">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="font-display text-3xl font-bold mb-3" style="color: var(--warm-900);">Thank You!</h1>
            <p class="text-lg mb-6" style="color: var(--warm-700);">Your review has been submitted and will appear once approved.</p>
            <a href="{{ route('storefront.menu') }}" class="btn-primary inline-block px-8 py-3">
                Back to Menu
            </a>
        </div>
    @else
        {{-- Review Form --}}
        <div class="text-center mb-8">
            <p class="font-script text-xl mb-2" style="color: var(--warm-500);">Share your experience</p>
            <h1 class="font-display text-3xl font-bold mb-2" style="color: var(--warm-900);">How was your order?</h1>
            <p style="color: var(--warm-700);">From {{ $storeName }} · Order #{{ $order->order_number }}</p>
        </div>

        {{-- Order Items --}}
        <div class="card p-5 mb-8" style="background: var(--warm-100);">
            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3" style="color: var(--warm-600);">Your Order</h3>
            @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm py-1">
                    <span style="color: var(--warm-900);">{{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}</span>
                    <span style="color: var(--warm-700);">${{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('storefront.store-review', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-medium mb-3" style="color: var(--warm-900);">Rating</label>
                <div class="flex gap-2 justify-center" x-data="{ rating: {{ $prefilledRating ?? 0 }} }">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button"
                            x-on:click="rating = {{ $i }}"
                            class="text-4xl transition-transform hover:scale-110 focus:outline-none"
                            :class="rating >= {{ $i }} ? 'grayscale-0' : 'grayscale opacity-40'"
                        >⭐</button>
                    @endfor
                    <input type="hidden" name="rating" x-bind:value="rating">
                </div>
                @error('rating')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Comment --}}
            <div>
                <label for="comment" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">Comments (optional)</label>
                <textarea name="comment" id="comment" rows="4" class="input-field" placeholder="Tell us about your experience...">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photo Upload --}}
            <div>
                <label for="photo" class="block text-sm font-medium mb-2" style="color: var(--warm-900);">Add a photo (optional)</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold" style="color: var(--warm-700);">
                @error('photo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full btn-primary py-3 text-lg">
                Submit Review
            </button>
        </form>
    @endif
</div>
@endsection
