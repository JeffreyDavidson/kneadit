@use(App\Enums\Orders\DeliveryType)
<x-layouts.storefront>
<x-slot:styles>
<link rel="stylesheet" href="{{ asset('css/order-confirmation.css') }}">
</x-slot:styles>

{{-- Photo-Forward Hero with Success --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Order confirmed" image-class="hero-img" min-height="40vh">

    <div class="relative z-10 text-center px-4 py-24 md:py-32 md:pt-24">
        {{-- Animated success checkmark --}}
        <x-storefront.icon-circle size="xl" variant="bold" inline class="mb-8 hero-fade-up [animation-delay:0.3s]">
            <x-heroicon-o-check class="w-12 h-12 text-warm-500" stroke-width="2.5" />
        </x-storefront.icon-circle>

        <x-storefront.eyebrow class="hero-fade-up mb-4 [animation-delay:0.5s]">{{ $content['hero_eyebrow'] ?? 'Order Placed' }}</x-storefront.eyebrow>

        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 hero-fade-up text-warm-100 [animation-delay:0.7s]">
            {{ $content['hero_title'] ?? 'Thank You!' }}
        </h1>
        <p class="text-lg mb-3 max-w-lg mx-auto hero-fade-up text-warm-400 [animation-delay:0.9s]">
            {{ $content['hero_description'] ?? 'Your order has been received and we\'ll start preparing your items right away.' }}
        </p>
        <div class="inline-block px-6 py-3 rounded-full hero-fade-up bg-warm-500/10 border border-warm-500/25 [animation-delay:1.1s]">
            <span class="text-sm font-medium text-warm-400">Order Number:</span>
            <span class="font-mono font-bold ml-2 text-warm-300">{{ $order->order_number }}</span>
        </div>
    </div>
</x-storefront.hero-section>

{{-- Order Details --}}
<section class="bg-warm-900">
    <div class="max-w-5xl mx-auto px-4 pb-24">
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Items & Totals --}}
            <div class="rounded-2xl p-6 md:p-8 bg-warm-800 border border-warm-700/20">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-8 h-px bg-warm-500"></span>
                    <h2 class="font-display text-xl font-semibold text-warm-100">{{ $content['details_heading'] ?? 'Order Details' }}</h2>
                </div>

                <div class="space-y-3 mb-6">
                    @foreach ($order->orderItems as $item)
                    <div class="flex justify-between items-center py-2 border-b border-warm-700/15">
                        <div>
                            <span class="font-medium text-warm-200">{{ $item->product->name ?? 'Product' }}</span>
                            <span class="text-sm ml-2 text-warm-500">× {{ $item->quantity }}</span>
                        </div>
                        <span class="font-semibold text-warm-300">@money($item->total_price)</span>
                    </div>
                    @endforeach
                </div>

                <div class="space-y-2 pt-4 text-sm border-t border-warm-700/20">
                    <div class="flex justify-between">
                        <span class="text-warm-500">Subtotal</span>
                        <span class="text-warm-300">@money($order->subtotal)</span>
                    </div>
                    @if ($order->delivery_fee->isPositive())
                    <div class="flex justify-between">
                        <span class="text-warm-500">Delivery Fee</span>
                        <span class="text-warm-300">@money($order->delivery_fee)</span>
                    </div>
                    @endif
                    @if ($order->discount_amount->isPositive())
                    <div class="flex justify-between text-green-400">
                        <span>Discount @if ($order->coupon)({{ $order->coupon->code }})@endif</span>
                        <span>-@money($order->discount_amount)</span>
                    </div>
                    @endif
                    @if ($order->gift_card_amount->isPositive())
                    <div class="flex justify-between text-green-400">
                        <span>Gift Card</span>
                        <span>-@money($order->gift_card_amount)</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-3 border-t border-warm-700/20">
                        <span class="font-display text-lg font-bold text-warm-100">Total</span>
                        <span class="font-display text-2xl font-bold text-warm-400">@money($order->total)</span>
                    </div>
                </div>
            </div>

            {{-- Customer & Delivery Info --}}
            <div class="rounded-2xl p-6 md:p-8 bg-warm-800 border border-warm-700/20">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-8 h-px bg-warm-500"></span>
                    <h2 class="font-display text-xl font-semibold text-warm-100">
                        {{ $order->delivery_type === DeliveryType::Delivery ? 'Delivery' : 'Pickup' }} Details
                    </h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Customer</span>
                        <p class="text-warm-200">{{ $order->customer->name }}</p>
                        <p class="text-sm text-warm-400">{{ $order->customer->email }}</p>
                        @if ($order->customer->phone)
                        <p class="text-sm text-warm-400">{{ $order->customer->phone }}</p>
                        @endif
                    </div>

                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Date & Time</span>
                        <p class="text-warm-200">{{ \Carbon\Carbon::parse($order->delivery_date)->format('l, F j, Y') }}</p>
                        @if ($order->delivery_time)
                        <p class="text-sm text-warm-400">{{ $order->delivery_time }}</p>
                        @endif
                    </div>

                    @if ($order->delivery_type === DeliveryType::Delivery && $order->delivery_address)
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Delivery Address</span>
                        <p class="text-warm-200">{{ $order->delivery_address }}</p>
                    </div>
                    @endif

                    @if ($order->notes)
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Special Instructions</span>
                        <p class="text-sm text-warm-300">{{ $order->notes }}</p>
                    </div>
                    @endif

                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Status</span>
                        <x-storefront.pill tone="outlined" size="md">
                            {{ $order->status->getLabel() }}
                        </x-storefront.pill>
                    </div>
                </div>
            </div>
        </div>

        {{-- What Happens Next --}}
        <div class="rounded-2xl p-8 md:p-12 mt-8 bg-warm-800 border border-warm-700/20">
            <div class="text-center mb-10">
                <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">{{ $content['journey_eyebrow'] ?? 'What Happens Next' }}</span>
                <h2 class="font-display text-3xl font-bold mt-2 text-warm-100">{{ $content['journey_heading'] ?? 'Your Order Journey' }}</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach ($journeySteps as $stepIndex => $step)
                <div class="text-center">
                    <x-storefront.icon-circle size="md" variant="tinted" class="mx-auto mb-4">
                        <span class="font-display text-xl font-bold text-warm-400">{{ $stepIndex + 1 }}</span>
                    </x-storefront.icon-circle>
                    <h3 class="font-display text-lg font-semibold mb-2 text-warm-200">
                        @if (isset($step['description_delivery']) || isset($step['description_pickup']))
                            {{ $order->delivery_type === DeliveryType::Delivery ? 'Delivery' : 'Pickup' }}
                        @else
                            {{ $step['title'] }}
                        @endif
                    </h3>
                    <p class="text-sm text-warm-500">
                        @if (isset($step['description_delivery']) || isset($step['description_pickup']))
                            @if ($order->delivery_type === DeliveryType::Delivery)
                                {{ $step['description_delivery'] ?? 'We\'ll deliver your fresh items right to your door.' }}
                            @else
                                {{ $step['description_pickup'] ?? 'Your items will be warm and ready for you to pick up.' }}
                            @endif
                        @else
                            {{ $step['description'] }}
                        @endif
                    </p>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-10 pt-8 border-t border-warm-700/20">
                <p class="text-sm mb-6 text-warm-500">
                    Questions? Reference order <strong class="text-warm-400">{{ $order->order_number }}</strong> when you
                    <a href="{{ route('contact.show') }}" class="underline text-warm-400">contact us</a>.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <x-storefront.button :href="route('order.track')" size="md">
                        Track Your Order
                    </x-storefront.button>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all text-warm-400">
                        Back to {{ $settings->store->name }}
                        <x-heroicon-o-arrow-right class="w-4 h-4" stroke-width="2" />
                    </a>
                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all text-warm-500">
                        <x-heroicon-o-printer class="w-4 h-4" stroke-width="2" />
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
</x-layouts.storefront>