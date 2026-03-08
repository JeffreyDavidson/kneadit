@extends('layouts.storefront')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    @if(isset($success) && $success)
        {{-- Success State --}}
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Thank You!</h1>
            <p class="text-lg text-gray-600 mb-6">Your review has been submitted and will appear once approved.</p>
            <a href="{{ route('storefront.menu') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                Back to Menu
            </a>
        </div>
    @else
        {{-- Review Form --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">How was your order?</h1>
            <p class="text-gray-600">From {{ $storeName }} · Order #{{ $order->order_number }}</p>
        </div>

        {{-- Order Items --}}
        <div class="bg-amber-50 rounded-xl p-5 mb-8">
            <h3 class="text-sm font-semibold text-amber-800 uppercase tracking-wide mb-3">Your Order</h3>
            @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-gray-700">{{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}</span>
                    <span class="text-gray-500">${{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('storefront.store-review', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Rating</label>
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
                <label for="comment" class="block text-sm font-semibold text-gray-700 mb-1">Comments (optional)</label>
                <textarea name="comment" id="comment" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Tell us about your experience...">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photo Upload --}}
            <div>
                <label for="photo" class="block text-sm font-semibold text-gray-700 mb-1">Add a photo (optional)</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                @error('photo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition text-lg">
                Submit Review
            </button>
        </form>
    @endif
</div>
@endsection
