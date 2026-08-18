@use(App\Enums\Orders\DeliveryType)
@use(App\Presenters\OrderItemPresenter)
<x-layouts.storefront>
    <x-slot:styles>
        <link rel="stylesheet" href="{{ asset('css/order-confirmation.css') }}" />
    </x-slot:styles>

    {{-- Photo-Forward Hero with Success --}}
    <x-storefront.hero-section
        :image="$settings->heroImageUrl()"
        image-alt="Order confirmed"
        image-class="hero-img"
        min-height="40vh"
    >
        <div class="relative z-10 px-4 py-24 text-center md:py-32 md:pt-24">
            {{-- Animated success checkmark --}}
            <x-storefront.icon-circle size="xl" variant="bold" inline class="hero-fade-1 mb-8">
                <x-heroicon-o-check class="text-warm-500 h-12 w-12" stroke-width="2.5" />
            </x-storefront.icon-circle>

            <x-storefront.eyebrow class="hero-fade-2 mb-4">
                {{ $content['hero_eyebrow'] ?? 'Order Placed' }}</x-storefront.eyebrow>

            <h1 class="font-display hero-fade-3 text-warm-100 mb-4 text-4xl font-bold md:text-6xl">
                {{ $content['hero_title'] ?? 'Thank You!' }}
            </h1>
            <p class="hero-fade-4 text-warm-100 mx-auto mb-3 max-w-lg text-lg">
                {{ $content['hero_description'] ?? 'Your order has been received and we\'ll start preparing your items right away.' }}
            </p>
            <div class="hero-fade-5 bg-warm-500/10 border-warm-500/25 inline-block rounded-full border px-6 py-3">
                <span class="text-warm-400 text-sm font-medium">Order Number:</span>
                <span class="text-warm-300 ml-2 font-mono font-bold">{{ $order->order_number }}</span>
            </div>
        </div>
    </x-storefront.hero-section>

    {{-- Order Details --}}
    <section class="bg-warm-900">
        <div class="mx-auto max-w-5xl px-4 pb-24">
            <div class="grid gap-8 md:grid-cols-2">
                {{-- Items & Totals --}}
                <div class="bg-warm-800 border-warm-700/20 rounded-2xl border p-6 md:p-8">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="bg-warm-500 block h-px w-8"></span>
                        <h2 class="font-display text-warm-100 text-xl font-semibold">
                            {{ $content['details_heading'] ?? 'Order Details' }}
                        </h2>
                    </div>

                    <div class="mb-6 space-y-3">
                        @foreach ($order->orderItems as $item)
                            <div class="border-warm-700/15 flex items-center justify-between border-b py-2">
                                <div>
                                    <span class="text-warm-200 font-medium">{{ $item->product->name ?? 'Product' }}</span>
                                    <span class="text-warm-500 ml-2 text-sm">× {{ $item->quantity }}</span>
                                </div>
                                <span class="text-warm-300 font-semibold">
                                    @money(OrderItemPresenter::for($item)->totalPrice())
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-warm-700/20 space-y-2 border-t pt-4 text-sm">
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
                                <span
                                    >Discount
                                    @if ($order->coupon) ({{ $order->coupon->code }})@endif
                                </span>
                                <span
                                    >-
                                    @money($order->discount_amount)
                                </span>
                            </div>
                        @endif
                        @if ($order->gift_card_amount->isPositive())
                            <div class="flex justify-between text-green-400">
                                <span>Gift Card</span>
                                <span
                                    >-
                                    @money($order->gift_card_amount)
                                </span>
                            </div>
                        @endif
                        @if ($order->tip_amount->isPositive())
                            <div class="flex justify-between">
                                <span class="text-warm-500">Tip</span>
                                <span class="text-warm-300">@money($order->tip_amount)</span>
                            </div>
                        @endif
                        <div class="border-warm-700/20 flex justify-between border-t pt-3">
                            <span class="font-display text-warm-100 text-lg font-bold">Total</span>
                            <span class="font-display text-warm-400 text-2xl font-bold">@money($order->total)</span>
                        </div>
                    </div>

                    @if ($canModify)
                        <div class="border-warm-700/20 mt-6 border-t pt-6" x-data="{ open: false }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-warm-300 text-sm font-semibold">Need to make changes?</p>
                                    <p class="text-warm-500 text-xs">
                                        {{ $modifyMinutesRemaining }} minute{{ $modifyMinutesRemaining === 1 ? '' : 's' }} left
                                        to modify your order.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="open = ! open"
                                    class="bg-warm-400 text-warm-900 hover:bg-warm-300 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                                >
                                    <span x-text="open ? 'Cancel' : 'Modify Order'"></span>
                                </button>
                            </div>

                            <form
                                x-show="open"
                                x-cloak
                                method="POST"
                                action="{{ route('order.modify', $order) }}"
                                class="mt-4 space-y-4"
                            >
                                @csrf
                                @error('items')
                                    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-300">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="space-y-2">
                                    @foreach ($order->orderItems as $index => $item)
                                        <div class="border-warm-700/15 flex items-center justify-between gap-3 border-b py-2">
                                            <span class="text-warm-300 flex-1 text-sm">{{ $item->product->name ?? 'Product' }}</span>
                                            <input
                                                type="hidden"
                                                name="items[{{ $index }}][order_item_id]"
                                                value="{{ $item->id }}"
                                            />
                                            <label for="modify-qty-{{ $item->id }}" class="sr-only"
                                                >Quantity for {{ $item->product->name ?? 'Product' }}</label>
                                            <input
                                                id="modify-qty-{{ $item->id }}"
                                                type="number"
                                                name="items[{{ $index }}][quantity]"
                                                min="0"
                                                max="20"
                                                value="{{ $item->quantity }}"
                                                class="border-warm-600/15 text-warm-300 w-20 rounded-lg border bg-white/[0.03] px-3 py-1 text-center text-sm"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label
                                        for="modify-tip"
                                        class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase"
                                    >Tip ($)</label>
                                    <input
                                        id="modify-tip"
                                        type="number"
                                        name="tip_amount"
                                        min="0"
                                        max="1000"
                                        step="0.01"
                                        value="{{ number_format($order->tip_amount->dollars(), 2, '.', '') }}"
                                        class="border-warm-600/15 text-warm-300 w-32 rounded-lg border bg-white/[0.03] px-3 py-2 text-sm"
                                    />
                                </div>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        class="bg-warm-400 text-warm-900 hover:bg-warm-300 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                                    >
                                        Save changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($referralCode)
                    <div class="bg-warm-800 border-warm-700/20 rounded-2xl border p-6 md:col-span-2 md:p-8">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="bg-warm-500 block h-px w-8"></span>
                            <h2 class="font-display text-warm-100 text-xl font-semibold">Refer a friend, both save</h2>
                        </div>
                        <p class="text-warm-400 mb-4 text-sm">
                            Share this link. When they place their first order, they save ${{ $settings->engagement->customerReferralDiscountDollars }} —
                            and we'll send you a coupon for the same amount.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input
                                id="referral-share-url"
                                type="text"
                                readonly
                                value="{{ $referralShareUrl }}"
                                class="border-warm-600/15 text-warm-300 flex-1 rounded-lg border bg-white/[0.03] px-3 py-2 font-mono text-sm"
                            />
                            <button
                                type="button"
                                onclick="
                                    navigator.clipboard.writeText(document.getElementById('referral-share-url').value);
                                    this.textContent = 'Copied!';
                                "
                                class="bg-warm-400 text-warm-900 hover:bg-warm-300 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                            >
                                Copy link
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Customer & Delivery Info --}}
                <div class="bg-warm-800 border-warm-700/20 rounded-2xl border p-6 md:p-8">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="bg-warm-500 block h-px w-8"></span>
                        <h2 class="font-display text-warm-100 text-xl font-semibold">
                            {{ $order->delivery_type === DeliveryType::Delivery ? 'Delivery' : 'Pickup' }} Details
                        </h2>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Customer</span>
                            <p class="text-warm-200">{{ $order->customer->name }}</p>
                            <p class="text-warm-400 text-sm">{{ $order->customer->email }}</p>
                            @if ($order->customer->phone)
                                <p class="text-warm-400 text-sm">{{ $order->customer->phone }}</p>
                            @endif
                        </div>

                        <div>
                            <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Date & Time</span>
                            <p class="text-warm-200">
                                {{ \Carbon\Carbon::parse($order->delivery_date)->format('l, F j, Y') }}
                            </p>
                            @if ($order->delivery_time)
                                <p class="text-warm-400 text-sm">{{ $order->delivery_time }}</p>
                            @endif
                        </div>

                        @if ($order->delivery_type === DeliveryType::Delivery && $order->delivery_address)
                            <div>
                                <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Delivery Address</span>
                                <p class="text-warm-200">{{ $order->delivery_address }}</p>
                            </div>
                        @endif

                        @if ($order->pickup_contact_name)
                            <div>
                                <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Picking up for you</span>
                                <p class="text-warm-200">{{ $order->pickup_contact_name }}</p>
                                @if ($order->pickup_contact_phone)
                                    <p class="text-warm-400 text-sm">{{ $order->pickup_contact_phone }}</p>
                                @endif
                                @if ($order->pickup_contact_email)
                                    <p class="text-warm-400 text-sm">{{ $order->pickup_contact_email }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($order->notes)
                            <div>
                                <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Special Instructions</span>
                                <p class="text-warm-300 text-sm">{{ $order->notes }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="text-warm-500 mb-1 block text-xs font-medium tracking-wider uppercase">Status</span>
                            <x-storefront.pill tone="outlined" size="md">
                                {{ $order->status->getLabel() }}
                            </x-storefront.pill>
                        </div>
                    </div>
                </div>
            </div>

            {{-- What Happens Next --}}
            <div class="bg-warm-800 border-warm-700/20 mt-8 rounded-2xl border p-8 md:p-12">
                <div class="mb-10 text-center">
                    <span class="text-warm-500 text-xs font-semibold tracking-[0.25em] uppercase">{{ $content['journey_eyebrow'] ?? 'What Happens Next' }}</span>
                    <h2 class="font-display text-warm-100 mt-2 text-3xl font-bold">
                        {{ $content['journey_heading'] ?? 'Your Order Journey' }}
                    </h2>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($journeySteps as $stepIndex => $step)
                        <div class="text-center">
                            <x-storefront.icon-circle size="md" variant="tinted" class="mx-auto mb-4">
                                <span class="font-display text-warm-400 text-xl font-bold">{{ $stepIndex + 1 }}</span>
                            </x-storefront.icon-circle>
                            <h3 class="font-display text-warm-200 mb-2 text-lg font-semibold">
                                @if (isset($step['description_delivery']) || isset($step['description_pickup']))
                                    {{ $order->delivery_type === DeliveryType::Delivery ? 'Delivery' : 'Pickup' }}
                                @else
                                    {{ $step['title'] }}
                                @endif
                            </h3>
                            <p class="text-warm-500 text-sm">
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

                <div class="border-warm-700/20 mt-10 border-t pt-8 text-center">
                    <p class="text-warm-500 mb-6 text-sm">
                        Questions? Reference order
                        <strong class="text-warm-400">{{ $order->order_number }}</strong> when you
                        <a href="{{ route('contact.show') }}" class="text-warm-400 underline">contact us</a>.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <x-storefront.button :href="route('order.track')" size="md">
                            Track Your Order
                        </x-storefront.button>
                        <a
                            href="{{ url('/') }}"
                            class="text-warm-400 inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all"
                        >
                            Back to {{ $settings->store->name }}
                            <x-heroicon-o-arrow-right class="h-4 w-4" stroke-width="2" />
                        </a>
                        <button
                            onclick="window.print()"
                            class="text-warm-500 inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all"
                        >
                            <x-heroicon-o-printer class="h-4 w-4" stroke-width="2" />
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
