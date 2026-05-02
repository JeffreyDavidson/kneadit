@php
/** @var \App\Models\Orders\Order $order */
/** @var \App\Services\Settings\TenantSettings $settings */
@endphp

<x-layouts.storefront>

<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Verify your email" image-class="hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="hero-fade-1 mb-6">Order Access</x-storefront.eyebrow>
        <h1 class="hero-fade-1 font-display text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
            Verify your email
        </h1>
        <p class="hero-fade-2 text-lg max-w-lg mx-auto text-warm-100">
            Enter the email address you used when placing this order to view its details.
        </p>
    </div>
</x-storefront.hero-section>

<section class="relative py-16 md:py-20 bg-warm-100">
    <div class="max-w-xl mx-auto px-4">
        <form method="POST" action="{{ route('order.verify.store', ['order' => $order->order_number]) }}" class="hero-fade-3">
            @csrf
            <label for="email" class="block text-xs font-medium uppercase tracking-[0.2em] mb-3 text-center text-warm-500">Email Address</label>
            <div class="flex gap-3">
                <input type="email" name="email" id="email" class="track-input flex-1"
                       placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
                <x-storefront.button type="submit" size="md" fontDisplay class="flex-shrink-0">
                    Verify
                </x-storefront.button>
            </div>
            @error('email')
                <p class="text-red-500 text-sm mt-3 text-center">{{ $message }}</p>
            @enderror
        </form>

        <p class="text-center text-sm text-warm-600 mt-8">
            Don't have the order email handy?
            <a href="{{ route('order.track') }}" class="text-warm-500 underline">Look up your orders instead</a>.
        </p>
    </div>
</section>

</x-layouts.storefront>
