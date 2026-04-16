<x-layouts.storefront>
{{-- Dark Hero Banner --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); padding-top: 2rem;">
    <x-storefront.grain-texture />
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 0%, color-mix(in srgb, var(--warm-500) 8%, transparent), transparent 60%);" aria-hidden="true"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 md:py-24">

        <x-storefront.eyebrow align="left" class="mb-6">Fresh From Our Ovens</x-storefront.eyebrow>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 text-warm-100">
            Place Your Order
        </h1>
        <p class="text-lg max-w-2xl text-warm-400">
            Choose your items, tell us when you need them, and we'll have everything freshly prepared.
            Orders need {{ $settings->leadTimeHours }} hours notice — ready {{ now()->addDays($settings->leadTimeDays())->format('l, F j') }} or later.
        </p>
    </div>
</section>

{{-- Main Content --}}
<section class="relative bg-warm-900">
    <x-storefront.grain-texture />

    <div class="relative z-10 max-w-7xl mx-auto px-4 pb-24"
         x-data="orderForm()"
         x-init="init()">

        <form @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
            {{-- Product Selection --}}
            <div class="lg:col-span-2 space-y-10">
                <div class="flex items-center gap-4">
                    <h2 class="font-display text-2xl font-bold whitespace-nowrap text-warm-100">Select Your Items</h2>
                    <div class="flex-1 h-px bg-warm-600/25"></div>
                </div>

                @foreach ($categories as $category)
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="block w-6 h-px bg-warm-500"></span>
                        <h3 class="font-display text-xl font-semibold text-warm-300">{{ $category->name }}</h3>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        @foreach ($category->products as $product)
                        @if ($product->is_active)
                        <div class="order-product-card" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                            {{-- Favorite Heart --}}
                            <button type="button"
                                    @click="toggleFavorite({{ $product->id }})"
                                    class="absolute top-3 right-3 z-10 w-11 h-11 rounded-full flex items-center justify-center backdrop-blur-sm transition-all bg-warm-900/60"
                                    :class="isFavorite({{ $product->id }}) ? '' : 'hover:scale-110'"
                                    :aria-label="isFavorite({{ $product->id }}) ? 'Remove ' + @js($product->name) + ' from favorites' : 'Add ' + @js($product->name) + ' to favorites'">
                                <svg x-show="isFavorite({{ $product->id }})" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                <svg x-show="!isFavorite({{ $product->id }})" class="w-5 h-5 text-warm-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 8.25c0-3.928-2.464-5.25-5.438-5.25A5.5 5.5 0 0012 5.052 5.5 5.5 0 007.688 3C4.714 3 2.25 4.322 2.25 8.25c0 3.925 2.438 7.111 4.739 9.256a25.175 25.175 0 004.244 3.17c.11.067.22.128.383.219l.022.012.007.004.003.001a.752.752 0 00.704 0l.003-.001.007-.004.022-.012a15.247 15.247 0 00.383-.219 25.175 25.175 0 004.244-3.17C19.314 15.36 21.752 12.175 21.752 8.25z"/></svg>
                            </button>

                            {{-- Product Image --}}
                            <div class="relative overflow-hidden aspect-[4/3]">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <x-storefront.image-placeholder :name="$product->name" />
                                @endif
                                {{-- Price badge --}}
                                <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full text-sm font-bold backdrop-blur-sm bg-warm-900/80 text-warm-400 border border-warm-500/20">
                                    @money($product->price)
                                </div>
                            </div>

                            <div class="p-5">
                                <h4 class="font-display text-lg font-semibold mb-1 text-warm-100">{{ $product->name }}</h4>
                                @if ($product->description)
                                <p class="text-sm mb-3 line-clamp-2 text-warm-500">{{ $product->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-2">
                                    <div x-show="getQuantity({{ $product->id }}) > 0" class="text-sm font-medium text-warm-400">
                                        <span x-text="getQuantity({{ $product->id }})"></span> in cart
                                    </div>
                                    <div x-show="getQuantity({{ $product->id }}) === 0"></div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                @click="decrementItem({{ $product->id }})"
                                                :disabled="getQuantity({{ $product->id }}) <= 0"
                                                class="order-qty-btn">−</button>
                                        <span class="font-bold min-w-[1.5rem] text-center text-sm text-warm-100"
                                              x-text="getQuantity({{ $product->id }})"></span>
                                        <button type="button"
                                                @click="incrementItem({{ $product->id }}, {{ $product->price }})"
                                                class="order-qty-btn">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-1">
                <div class="order-sidebar-card p-6 md:p-8 sticky top-24">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="block w-6 h-px bg-warm-500"></span>
                        <h3 class="font-display text-xl font-semibold text-warm-100">Your Order</h3>
                    </div>

                    {{-- Cart Items --}}
                    <div class="space-y-2 mb-4" x-show="cartItems.length > 0">
                        <template x-for="item in cartItems" :key="item.id">
                            <div class="flex justify-between items-center text-sm py-2 px-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                                <span class="text-warm-400">
                                    <span class="font-semibold text-warm-300" x-text="item.quantity"></span> × <span x-text="item.name"></span>
                                </span>
                                <span class="font-semibold text-warm-300" x-text="'$' + (item.quantity * item.price).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>

                    <div x-show="cartItems.length === 0" class="text-center py-8 mb-4">
                        <div class="text-4xl mb-3 opacity-30">🧺</div>
                        <p class="text-sm text-warm-600">Your cart is empty</p>
                        <p class="text-xs mt-1 text-warm-700">Add items to get started</p>
                    </div>

                    {{-- Coupon Section --}}
                    <div class="pt-4 mb-4 border-t border-warm-700/20">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2 text-warm-500">Coupon Code</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="couponCode"
                                   placeholder="Enter coupon"
                                   class="order-input flex-1">
                            <button type="button"
                                    @click="applyCoupon()"
                                    :disabled="!couponCode || isApplyingCoupon"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all bg-warm-500/15 text-warm-400 border border-warm-500/30"
                                    :class="isApplyingCoupon ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                                <span x-text="isApplyingCoupon ? '...' : 'Apply'"></span>
                            </button>
                        </div>
                        <div x-show="couponError" class="text-red-400 text-sm mt-2" x-text="couponError"></div>
                        <div x-show="appliedCoupon" class="text-green-400 text-sm mt-2">
                            ✓ <span x-text="appliedCoupon?.label"></span> applied!
                        </div>
                    </div>

                    {{-- Gift Card Section --}}
                    <div class="pt-4 mb-4 border-t border-warm-700/20">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2 text-warm-500">Gift Card</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="giftCardCode"
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="order-input flex-1 font-mono uppercase tracking-wider text-sm">
                            <button type="button"
                                    @click="applyGiftCard()"
                                    :disabled="!giftCardCode || isApplyingGiftCard"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all bg-warm-500/15 text-warm-400 border border-warm-500/30"
                                    :class="isApplyingGiftCard ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'">
                                <span x-text="isApplyingGiftCard ? '...' : 'Apply'"></span>
                            </button>
                        </div>
                        <div x-show="giftCardError" class="text-red-400 text-sm mt-2" x-text="giftCardError"></div>
                        <div x-show="appliedGiftCard" class="text-green-400 text-sm mt-2">
                            ✓ Gift card applied! Balance: $<span x-text="appliedGiftCard?.available_balance?.toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="pt-4 space-y-2 text-sm border-t border-warm-700/20">
                        <div class="flex justify-between">
                            <span class="text-warm-500">Subtotal</span>
                            <span class="text-warm-300" x-text="'$' + subtotal.toFixed(2)"></span>
                        </div>
                        <div x-show="deliveryFee > 0" class="flex justify-between">
                            <span class="text-warm-500">Delivery</span>
                            <span class="text-warm-300" x-text="'$' + deliveryFee.toFixed(2)"></span>
                        </div>
                        <div x-show="appliedCoupon" class="flex justify-between text-green-400">
                            <span>Discount</span>
                            <span x-text="'-$' + discountAmount.toFixed(2)"></span>
                        </div>
                        <div x-show="appliedGiftCard" class="flex justify-between text-green-400">
                            <span>Gift Card</span>
                            <span x-text="'-$' + giftCardAmount.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-warm-700/20">
                            <span class="font-display text-lg font-bold text-warm-100">Total</span>
                            <span class="font-display text-2xl font-bold text-warm-400" x-text="'$' + total.toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Customer Information --}}
                    <div class="pt-6 mt-6 border-t border-warm-700/20">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold text-warm-500">Your Details</span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Name *</label>
                                <input type="text" x-model="form.customer_name" required class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Email *</label>
                                <input type="email" x-model="form.customer_email" @input="saveEmail()" required class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Phone</label>
                                <input type="tel" x-model="form.customer_phone" class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Birthday <span class="text-warm-600">(for special treats 🎂)</span></label>
                                <input type="date" x-model="form.customer_birthday" class="order-input" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Options --}}
                    <div class="pt-6 mt-6 border-t border-warm-700/20">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold text-warm-500">Delivery</span>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer px-4 py-3 rounded-xl transition-all bg-white/[0.03] border border-warm-600/15"
                                   :class="form.delivery_type === 'pickup' ? 'border-warm-500 bg-warm-500/[0.08]' : ''">
                                <input type="radio" x-model="form.delivery_type" value="pickup" @change="calculateDeliveryFee()" class="order-radio mr-3">
                                <span class="text-warm-200">Pickup <span class="text-sm text-warm-500">(Free)</span></span>
                            </label>

                            @if ($settings->deliveryEnabled)
                            <label class="flex items-center cursor-pointer px-4 py-3 rounded-xl transition-all bg-white/[0.03] border border-warm-600/15"
                                   :class="form.delivery_type === 'delivery' ? 'border-warm-500 bg-warm-500/[0.08]' : ''">
                                <input type="radio" x-model="form.delivery_type" value="delivery" @change="calculateDeliveryFee()" class="order-radio mr-3">
                                <span class="text-warm-200">Delivery</span>
                            </label>
                            @endif
                        </div>

                        @if ($settings->deliveryEnabled)
                        <div x-show="form.delivery_type === 'delivery'" class="mt-4 space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Delivery Address *</label>
                                <textarea x-model="form.delivery_address" placeholder="Full address" class="order-input" rows="3"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Distance</label>
                                <select x-model="form.delivery_tier" @change="calculateDeliveryFee()" class="order-input">
                                    <option value="">Select distance</option>
                                    @foreach ($settings->deliveryFeeTiers as $index => $tier)
                                    <option value="{{ $index }}">{{ $tier['description'] }} (@money($tier['fee']))</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($settings->freeDeliveryMinimum)
                            <p class="text-sm text-warm-500">
                                🚚 Free delivery on orders over @money((float)$settings->freeDeliveryMinimum)!
                            </p>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Date & Time --}}
                    <div class="pt-6 mt-6 border-t border-warm-700/20">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold text-warm-500">When</span>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">
                                    <span x-text="form.delivery_type === 'delivery' ? 'Delivery Date' : 'Pickup Date'"></span> *
                                </label>
                                <input type="date" x-model="form.delivery_date" :min="minDate" @change="checkCapacity()" required class="order-input">
                                <div x-show="capacityWarning" class="text-amber-400 text-sm mt-1" x-text="capacityWarning"></div>
                                <div x-show="capacityError" class="text-red-400 text-sm mt-1" x-text="capacityError"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-warm-400">Preferred Time</label>
                                <input type="text" x-model="form.delivery_time" placeholder="e.g., 10:00 AM" class="order-input">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="pt-6 mt-6 border-t border-warm-700/20">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2 text-warm-500">Special Instructions</label>
                        <textarea x-model="form.notes" placeholder="Allergies, decorations, anything..." class="order-input" rows="3"></textarea>
                    </div>

                    @if (!empty($settings->paymentMethodsAccepted))
                    <div class="pt-4 mt-4 border-t border-warm-700/20">
                        <p class="text-xs text-warm-600">
                            <span class="font-medium text-warm-500">Payment:</span> {{ implode(', ', array_map('ucfirst', $settings->paymentMethodsAccepted)) }}
                        </p>
                    </div>
                    @endif

                    @if ($settings->allergyDisclaimer)
                    <div class="pt-4 mt-4 border-t border-warm-700/20">
                        <p class="text-xs leading-relaxed text-warm-600">
                            <strong class="text-warm-500">⚠ Allergy Notice:</strong> {{ $settings->allergyDisclaimer }}
                        </p>
                    </div>
                    @endif

                    {{-- Submit --}}
                    <button type="submit"
                            :disabled="!canSubmit || isSubmitting"
                            class="w-full mt-6 py-4 rounded-full text-lg font-semibold transition-all duration-300"
                            :class="!canSubmit || isSubmitting ? 'opacity-30 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-lg'"
                            style="background: var(--warm-500); color: var(--warm-900); font-family: var(--font-display);">
                        <span x-text="isSubmitting ? 'Placing Order...' : 'Place Order →'"></span>
                    </button>

                    <div x-show="submitError" class="text-red-400 text-sm mt-3 text-center" x-text="submitError"></div>
                </div>
            </div>
        </form>
    </div>
</section>
@include('partials.order-form-script')
</x-layouts.storefront>