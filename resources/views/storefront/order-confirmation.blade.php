@use(App\Enums\Orders\DeliveryType)
@use(App\Presenters\OrderItemPresenter)
<x-layouts.storefront>
<x-slot:styles>
<link rel="stylesheet" href="{{ asset('css/order-confirmation.css') }}">
</x-slot:styles>

{{-- Photo-Forward Hero with Success --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Order confirmed" image-class="hero-img" min-height="40vh">

 <div class="relative z-10 text-center px-4 py-24 md:py-32 md:pt-24">
 {{-- Animated success checkmark --}}
 <x-storefront.icon-circle size="xl" variant="bold" inline class="mb-8 hero-fade-1">
 <x-heroicon-o-check class="w-12 h-12 text-warm-500" stroke-width="2.5" />
 </x-storefront.icon-circle>

 <x-storefront.eyebrow class="hero-fade-2 mb-4">{{ $content['hero_eyebrow'] ?? 'Order Placed' }}</x-storefront.eyebrow>

 <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 hero-fade-3 text-warm-100">
 {{ $content['hero_title'] ?? 'Thank You!' }}
 </h1>
 <p class="text-lg mb-3 max-w-lg mx-auto hero-fade-4 text-warm-400">
 {{ $content['hero_description'] ?? 'Your order has been received and we\'ll start preparing your items right away.' }}
 </p>
 <div class="inline-block px-6 py-3 rounded-full hero-fade-5 bg-warm-500/10 border border-warm-500/25">
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
 <span class="font-semibold text-warm-300">@money(OrderItemPresenter::for($item)->totalPrice())</span>
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
 @if ($order->tip_amount->isPositive())
 <div class="flex justify-between">
 <span class="text-warm-500">Tip</span>
 <span class="text-warm-300">@money($order->tip_amount)</span>
 </div>
 @endif
 <div class="flex justify-between pt-3 border-t border-warm-700/20">
 <span class="font-display text-lg font-bold text-warm-100">Total</span>
 <span class="font-display text-2xl font-bold text-warm-400">@money($order->total)</span>
 </div>
 </div>

 @if ($canModify)
 <div class="mt-6 pt-6 border-t border-warm-700/20" x-data="{ open: false }">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-semibold text-warm-300">Need to make changes?</p>
 <p class="text-xs text-warm-500">{{ $modifyMinutesRemaining }} minute{{ $modifyMinutesRemaining === 1 ? '' : 's' }} left to modify your order.</p>
 </div>
 <button type="button" @click="open = !open"
 class="px-4 py-2 rounded-lg text-sm font-semibold bg-warm-400 text-warm-900 hover:bg-warm-300 transition-all">
 <span x-text="open ? 'Cancel' : 'Modify Order'"></span>
 </button>
 </div>

 <form x-show="open" x-cloak method="POST" action="{{ route('order.modify', $order) }}" class="mt-4 space-y-4">
 @csrf
 @error('items')
 <div class="px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm">{{ $message }}</div>
 @enderror
 <div class="space-y-2">
 @foreach ($order->orderItems as $index => $item)
 <div class="flex items-center justify-between gap-3 py-2 border-b border-warm-700/15">
 <span class="flex-1 text-sm text-warm-300">{{ $item->product->name ?? 'Product' }}</span>
 <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
 <label for="modify-qty-{{ $item->id }}" class="sr-only">Quantity for {{ $item->product->name ?? 'Product' }}</label>
 <input id="modify-qty-{{ $item->id }}" type="number" name="items[{{ $index }}][quantity]"
 min="0" max="20" value="{{ $item->quantity }}"
 class="w-20 px-3 py-1 rounded-lg bg-white/[0.03] border border-warm-600/15 text-warm-300 text-sm text-center" />
 </div>
 @endforeach
 </div>
 <div>
 <label for="modify-tip" class="block text-xs uppercase tracking-wider font-medium mb-1 text-warm-500">Tip ($)</label>
 <input id="modify-tip" type="number" name="tip_amount" min="0" max="1000" step="0.01"
 value="{{ number_format($order->tip_amount->dollars(), 2, '.', '') }}"
 class="w-32 px-3 py-2 rounded-lg bg-white/[0.03] border border-warm-600/15 text-warm-300 text-sm" />
 </div>
 <div class="flex justify-end">
 <button type="submit"
 class="px-4 py-2 rounded-lg text-sm font-semibold bg-warm-400 text-warm-900 hover:bg-warm-300 transition-all">
 Save changes
 </button>
 </div>
 </form>
 </div>
 @endif
 </div>

 @if ($referralCode)
 <div class="md:col-span-2 rounded-2xl p-6 md:p-8 bg-warm-800 border border-warm-700/20">
 <div class="flex items-center gap-3 mb-4">
 <span class="block w-8 h-px bg-warm-500"></span>
 <h2 class="font-display text-xl font-semibold text-warm-100">Refer a friend, both save</h2>
 </div>
 <p class="text-sm text-warm-400 mb-4">Share this link. When they place their first order, they save ${{ $settings->engagement->customerReferralDiscountDollars }} — and we'll send you a coupon for the same amount.</p>
 <div class="flex flex-col sm:flex-row gap-3">
 <input id="referral-share-url" type="text" readonly value="{{ $referralShareUrl }}"
 class="flex-1 px-3 py-2 rounded-lg bg-white/[0.03] border border-warm-600/15 text-warm-300 text-sm font-mono" />
 <button type="button"
 onclick="navigator.clipboard.writeText(document.getElementById('referral-share-url').value); this.textContent='Copied!';"
 class="px-4 py-2 rounded-lg text-sm font-semibold bg-warm-400 text-warm-900 hover:bg-warm-300 transition-all">
 Copy link
 </button>
 </div>
 </div>
 @endif

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