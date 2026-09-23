@props(['content'])

<div class="mb-6 flex items-center gap-3">
    <span class="bg-warm-500 block h-px w-6"></span>
    <h3 class="font-display text-warm-100 text-xl font-semibold">Your Order</h3>
</div>

{{-- Cart Items --}}
<div class="mb-4 space-y-2" x-show="cartItems.length > 0">
    <template x-for="item in cartItems" :key="item.id">
        <div class="flex items-center justify-between rounded-lg bg-white/[0.03] px-3 py-2 text-sm">
            <span class="text-warm-400">
                <span class="text-warm-300 font-semibold" x-text="item.quantity"></span> ×
                <span x-text="item.name"></span>
            </span>
            <span class="text-warm-300 font-semibold" x-text="'$' + (item.quantity * item.price).toFixed(2)"></span>
        </div>
    </template>
</div>

<div x-show="cartItems.length === 0" class="mb-4 py-8 text-center">
    <div class="mb-3 text-4xl opacity-30">🧺</div>
    <p class="text-warm-300 text-sm">{{ $content['empty_cart_heading'] ?? 'Your cart is empty' }}</p>
    <p class="text-warm-500 mt-1 text-xs">{{ $content['empty_cart_subtext'] ?? 'Add items to get started' }}</p>
</div>

{{-- Coupon Section --}}
<div class="border-warm-700/20 mb-4 border-t pt-4">
    <label class="text-warm-500 mb-2 block text-xs font-medium tracking-wider uppercase">Coupon Code</label>
    <div class="flex gap-2">
        <input
            type="text"
            data-test="order-form-coupon-code"
            x-model="couponCode"
            placeholder="Enter coupon"
            class="order-input flex-1"
        />
        <button type="button"
                                    @click="applyCoupon()"
                                    :disabled="! couponCode || isApplyingCoupon"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all bg-warm-500/15 text-warm-400 border border-warm-500/30"
                                    :class="isApplyingCoupon ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
        <span x-text="isApplyingCoupon ? '...' : {{ Js::from($content['apply_button'] ?? 'Apply') }}"></span>
        </button>
    </div>
    <div x-show="couponError" class="mt-2 text-sm text-red-400" x-text="couponError"></div>
    <div x-show="appliedCoupon" class="mt-2 text-sm text-green-400">
        ✓ <span x-text="appliedCoupon?.label"></span> applied!
    </div>
</div>

{{-- Gift Card Section --}}
<div class="border-warm-700/20 mb-4 border-t pt-4">
    <label class="text-warm-500 mb-2 block text-xs font-medium tracking-wider uppercase">Gift Card</label>
    <div class="flex gap-2">
        <input
            type="text"
            data-test="order-form-gift-card-code"
            x-model="giftCardCode"
            placeholder="XXXX-XXXX-XXXX-XXXX"
            class="order-input flex-1 font-mono text-sm tracking-wider uppercase"
        />
        <button
            type="button"
            @click="applyGiftCard()"
            :disabled="! giftCardCode || isApplyingGiftCard"
            class="bg-warm-500/15 text-warm-400 border-warm-500/30 rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
            :class="isApplyingGiftCard ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
        >
            <span x-text="isApplyingGiftCard ? '...' : {{ Js::from($content['apply_button'] ?? 'Apply') }}"></span>
        </button>
    </div>
    <div x-show="giftCardError" class="mt-2 text-sm text-red-400" x-text="giftCardError"></div>
    <div x-show="appliedGiftCard" class="mt-2 text-sm text-green-400">
        ✓ Gift card applied! Balance: $<span x-text="appliedGiftCard?.available_balance?.toFixed(2)"></span>
    </div>
</div>

{{-- Tip --}}
<div x-show="cartItems.length > 0" class="border-warm-700/20 mt-4 border-t pt-4">
    <div class="mb-3 flex items-center gap-2">
        <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">Add a Tip</span>
    </div>
    <div class="grid grid-cols-5 gap-2">
        <button
            type="button"
            @click="selectTipPreset(0)"
            :class="tipMode === 'preset' && tipPercent === 0
                ? 'bg-warm-400 text-warm-900'
                : 'bg-white/[0.03] text-warm-400'"
            class="border-warm-600/15 rounded-lg border px-2 py-2 text-xs font-semibold transition-all"
        >
            None
        </button>
        <button
            type="button"
            @click="selectTipPreset(15)"
            :class="tipMode === 'preset' && tipPercent === 15
                ? 'bg-warm-400 text-warm-900'
                : 'bg-white/[0.03] text-warm-400'"
            class="border-warm-600/15 rounded-lg border px-2 py-2 text-xs font-semibold transition-all"
        >
            15%
        </button>
        <button
            type="button"
            @click="selectTipPreset(18)"
            :class="tipMode === 'preset' && tipPercent === 18
                ? 'bg-warm-400 text-warm-900'
                : 'bg-white/[0.03] text-warm-400'"
            class="border-warm-600/15 rounded-lg border px-2 py-2 text-xs font-semibold transition-all"
        >
            18%
        </button>
        <button
            type="button"
            @click="selectTipPreset(20)"
            :class="tipMode === 'preset' && tipPercent === 20
                ? 'bg-warm-400 text-warm-900'
                : 'bg-white/[0.03] text-warm-400'"
            class="border-warm-600/15 rounded-lg border px-2 py-2 text-xs font-semibold transition-all"
        >
            20%
        </button>
        <button
            type="button"
            @click="selectCustomTip()"
            :class="tipMode === 'custom' ? 'bg-warm-400 text-warm-900' : 'bg-white/[0.03] text-warm-400'"
            class="border-warm-600/15 rounded-lg border px-2 py-2 text-xs font-semibold transition-all"
        >
            Custom
        </button>
    </div>
    <div x-show="tipMode === 'custom'" class="mt-3">
        <label for="custom-tip-input" class="sr-only">Custom tip amount in dollars</label>
        <div class="flex items-center gap-2">
            <span class="text-warm-500">$</span>
            <input
                id="custom-tip-input"
                type="number"
                min="0"
                step="0.01"
                inputmode="decimal"
                x-model="customTip"
                @input="calculateTotals()"
                class="border-warm-600/15 text-warm-300 flex-1 rounded-lg border bg-white/[0.03] px-3 py-2 text-sm"
            />
        </div>
    </div>
</div>

{{-- Totals --}}
<div class="border-warm-700/20 space-y-2 border-t pt-4 text-sm">
    <div class="flex justify-between">
        <span class="text-warm-500">Subtotal</span>
        <span class="text-warm-300" x-text="'$' + subtotal.toFixed(2)"></span>
    </div>
    <div x-show="deliveryFee > 0" class="flex justify-between">
        <span class="text-warm-500">Delivery</span>
        <span class="text-warm-300" x-text="'$' + deliveryFee.toFixed(2)"></span>
    </div>
    <div x-show="saleDiscount > 0" class="flex justify-between text-green-400">
        <span x-text="sitewideSaleLabel + ' (' + sitewideSalePercent + '% off)'"></span>
        <span x-text="'-$' + saleDiscount.toFixed(2)"></span>
    </div>
    <div x-show="appliedCoupon" class="flex justify-between text-green-400">
        <span>Coupon</span>
        <span x-text="'-$' + discountAmount.toFixed(2)"></span>
    </div>
    <div x-show="appliedGiftCard" class="flex justify-between text-green-400">
        <span>Gift Card</span>
        <span x-text="'-$' + giftCardAmount.toFixed(2)"></span>
    </div>
    <div x-show="tipAmount > 0" class="flex justify-between">
        <span class="text-warm-500">Tip</span>
        <span class="text-warm-300" x-text="'$' + tipAmount.toFixed(2)"></span>
    </div>
    <div class="border-warm-700/20 flex justify-between border-t pt-3">
        <span class="font-display text-warm-100 text-lg font-bold">Total</span>
        <span class="font-display text-warm-400 text-2xl font-bold" x-text="'$' + total.toFixed(2)"></span>
    </div>
</div>
