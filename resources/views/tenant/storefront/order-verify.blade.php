@php
    /** @var \App\Models\Orders\Order $order */
    /** @var \App\Services\Settings\TenantSettings $settings */
@endphp

<x-layouts.storefront>
    <div @class(['biscotto-order-followup biscotto-order-verify' => $storefrontTheme === 'biscotto'])>
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Verify your email"
            image-class="hero-img"
        >
            <div class="relative z-10 flex min-h-[55vh] flex-col items-center justify-end px-4 pb-20 text-center">
                <x-storefront.eyebrow class="hero-fade-1 mb-6">Order Access</x-storefront.eyebrow>
                <h1 class="hero-fade-1 font-display text-warm-100 mb-6 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                    Verify your email
                </h1>
                <p class="hero-fade-2 text-warm-100 mx-auto max-w-lg text-lg">
                    Enter the email address you used when placing this order to view its details.
                </p>
            </div>
        </x-storefront.hero-section>

        <section class="bg-warm-100 relative py-16 md:py-20">
            <div class="mx-auto max-w-xl px-4">
                <form
                    method="POST"
                    action="{{ route('order.verify.store', ['order' => $order->order_number]) }}"
                    class="hero-fade-3"
                >
                    @csrf
                    <label
                        for="email"
                        class="text-warm-500 mb-3 block text-center text-xs font-medium tracking-[0.2em] uppercase"
                    >Email Address</label>
                    <div class="flex gap-3">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="track-input flex-1"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        />
                        <x-storefront.button type="submit" size="md" fontDisplay class="flex-shrink-0">
                            Verify
                        </x-storefront.button>
                    </div>
                    @error('email')
                        <p class="mt-3 text-center text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </form>

                <p class="text-warm-600 mt-8 text-center text-sm">
                    Don't have the order email handy?
                    <a href="{{ route('order.track') }}" class="text-warm-500 underline">Look up your orders instead</a
                    >.
                </p>
            </div>
        </section>
    </div>
</x-layouts.storefront>
