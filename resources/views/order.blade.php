<x-layouts.storefront>
{{-- Dark Hero Banner --}}
<section class="relative overflow-hidden" style="background: var(--warm-900); padding-top: 2rem;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 0%, rgba(212,146,12,0.08), transparent 60%);"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 md:py-24">

        <div class="flex items-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Fresh From Our Ovens</span>
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4" style="color: var(--warm-100);">
            Place Your Order
        </h1>
        <p class="text-lg max-w-2xl" style="color: var(--warm-400);">
            Choose your items, tell us when you need them, and we'll have everything freshly prepared.
            Orders need {{ $leadTimeHours }} hours notice — ready {{ date('l, F j', strtotime('+' . $leadTimeDays . ' days')) }} or later.
        </p>
    </div>
</section>

{{-- Main Content --}}
<section class="relative" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 pb-24"
         x-data="orderForm()"
         x-init="init()">

        <form @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
            {{-- Product Selection --}}
            <div class="lg:col-span-2 space-y-10">
                <div class="flex items-center gap-4">
                    <h2 class="font-display text-2xl font-bold whitespace-nowrap" style="color: var(--warm-100);">Select Your Items</h2>
                    <div class="flex-1 h-px" style="background: rgba(139,104,68,0.25);"></div>
                </div>

                @foreach ($categories as $category)
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="block w-6 h-px" style="background: var(--warm-500);"></span>
                        <h3 class="font-display text-xl font-semibold" style="color: var(--warm-300);">{{ $category->name }}</h3>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        @foreach ($category->products as $product)
                        @if ($product->is_active)
                        <div class="order-product-card" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                            {{-- Favorite Heart --}}
                            <button type="button"
                                    @click="toggleFavorite({{ $product->id }})"
                                    class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full flex items-center justify-center backdrop-blur-sm transition-all"
                                    style="background: rgba(28,20,16,0.6);"
                                    :class="isFavorite({{ $product->id }}) ? '' : 'hover:scale-110'">
                                <span x-text="isFavorite({{ $product->id }}) ? '❤️' : '🤍'" class="text-lg"></span>
                            </button>

                            {{-- Product Image --}}
                            <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-800));">
                                        <span class="text-5xl font-display font-bold" style="color: var(--warm-600); opacity: 0.3;">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                {{-- Price badge --}}
                                <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full text-sm font-bold backdrop-blur-sm" style="background: rgba(28,20,16,0.8); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.2);">
                                    ${{ number_format($product->price, 2) }}
                                </div>
                            </div>

                            <div class="p-5">
                                <h4 class="font-display text-lg font-semibold mb-1" style="color: var(--warm-100);">{{ $product->name }}</h4>
                                @if ($product->description)
                                <p class="text-sm mb-3 line-clamp-2" style="color: var(--warm-500);">{{ $product->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-2">
                                    <div x-show="getQuantity({{ $product->id }}) > 0" class="text-sm font-medium" style="color: var(--warm-400);">
                                        <span x-text="getQuantity({{ $product->id }})"></span> in cart
                                    </div>
                                    <div x-show="getQuantity({{ $product->id }}) === 0"></div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                @click="decrementItem({{ $product->id }})"
                                                :disabled="getQuantity({{ $product->id }}) <= 0"
                                                class="order-qty-btn">−</button>
                                        <span class="font-bold min-w-[1.5rem] text-center text-sm" style="color: var(--warm-100);"
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
                        <span class="block w-6 h-px" style="background: var(--warm-500);"></span>
                        <h3 class="font-display text-xl font-semibold" style="color: var(--warm-100);">Your Order</h3>
                    </div>

                    {{-- Cart Items --}}
                    <div class="space-y-2 mb-4" x-show="cartItems.length > 0">
                        <template x-for="item in cartItems" :key="item.id">
                            <div class="flex justify-between items-center text-sm py-2 px-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                                <span style="color: var(--warm-400);">
                                    <span class="font-semibold" style="color: var(--warm-300);" x-text="item.quantity"></span> × <span x-text="item.name"></span>
                                </span>
                                <span class="font-semibold" style="color: var(--warm-300);" x-text="'$' + (item.quantity * item.price).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>

                    <div x-show="cartItems.length === 0" class="text-center py-8 mb-4">
                        <div class="text-4xl mb-3 opacity-30">🧺</div>
                        <p class="text-sm" style="color: var(--warm-600);">Your cart is empty</p>
                        <p class="text-xs mt-1" style="color: var(--warm-700);">Add items to get started</p>
                    </div>

                    {{-- Coupon Section --}}
                    <div class="pt-4 mb-4" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2" style="color: var(--warm-500);">Coupon Code</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="couponCode"
                                   placeholder="Enter coupon"
                                   class="order-input flex-1">
                            <button type="button"
                                    @click="applyCoupon()"
                                    :disabled="!couponCode || isApplyingCoupon"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                                    :class="isApplyingCoupon ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                                    style="background: rgba(212,146,12,0.15); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.3);">
                                <span x-text="isApplyingCoupon ? '...' : 'Apply'"></span>
                            </button>
                        </div>
                        <div x-show="couponError" class="text-red-400 text-sm mt-2" x-text="couponError"></div>
                        <div x-show="appliedCoupon" class="text-green-400 text-sm mt-2">
                            ✓ <span x-text="appliedCoupon?.label"></span> applied!
                        </div>
                    </div>

                    {{-- Gift Card Section --}}
                    <div class="pt-4 mb-4" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2" style="color: var(--warm-500);">Gift Card</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="giftCardCode"
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="order-input flex-1 font-mono uppercase tracking-wider text-sm">
                            <button type="button"
                                    @click="applyGiftCard()"
                                    :disabled="!giftCardCode || isApplyingGiftCard"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                                    :class="isApplyingGiftCard ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                                    style="background: rgba(212,146,12,0.15); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.3);">
                                <span x-text="isApplyingGiftCard ? '...' : 'Apply'"></span>
                            </button>
                        </div>
                        <div x-show="giftCardError" class="text-red-400 text-sm mt-2" x-text="giftCardError"></div>
                        <div x-show="appliedGiftCard" class="text-green-400 text-sm mt-2">
                            ✓ Gift card applied! Balance: $<span x-text="appliedGiftCard?.available_balance?.toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="pt-4 space-y-2 text-sm" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <div class="flex justify-between">
                            <span style="color: var(--warm-500);">Subtotal</span>
                            <span style="color: var(--warm-300);" x-text="'$' + subtotal.toFixed(2)"></span>
                        </div>
                        <div x-show="deliveryFee > 0" class="flex justify-between">
                            <span style="color: var(--warm-500);">Delivery</span>
                            <span style="color: var(--warm-300);" x-text="'$' + deliveryFee.toFixed(2)"></span>
                        </div>
                        <div x-show="appliedCoupon" class="flex justify-between" style="color: #4ade80;">
                            <span>Discount</span>
                            <span x-text="'-$' + discountAmount.toFixed(2)"></span>
                        </div>
                        <div x-show="appliedGiftCard" class="flex justify-between" style="color: #4ade80;">
                            <span>Gift Card</span>
                            <span x-text="'-$' + giftCardAmount.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between pt-3" style="border-top: 1px solid rgba(139,104,68,0.2);">
                            <span class="font-display text-lg font-bold" style="color: var(--warm-100);">Total</span>
                            <span class="font-display text-2xl font-bold" style="color: var(--warm-400);" x-text="'$' + total.toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Customer Information --}}
                    <div class="pt-6 mt-6" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold" style="color: var(--warm-500);">Your Details</span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Name *</label>
                                <input type="text" x-model="form.customer_name" required class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Email *</label>
                                <input type="email" x-model="form.customer_email" @input="saveEmail()" required class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Phone</label>
                                <input type="tel" x-model="form.customer_phone" class="order-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Birthday <span style="color: var(--warm-600);">(for special treats 🎂)</span></label>
                                <input type="date" x-model="form.customer_birthday" class="order-input" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Options --}}
                    <div class="pt-6 mt-6" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold" style="color: var(--warm-500);">Delivery</span>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer px-4 py-3 rounded-xl transition-all" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(139,104,68,0.15);"
                                   :style="form.delivery_type === 'pickup' ? 'border-color: var(--warm-500); background: rgba(212,146,12,0.08);' : ''">
                                <input type="radio" x-model="form.delivery_type" value="pickup" @change="calculateDeliveryFee()" class="order-radio mr-3">
                                <span style="color: var(--warm-200);">Pickup <span class="text-sm" style="color: var(--warm-500);">(Free)</span></span>
                            </label>

                            @if ($deliveryEnabled)
                            <label class="flex items-center cursor-pointer px-4 py-3 rounded-xl transition-all" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(139,104,68,0.15);"
                                   :style="form.delivery_type === 'delivery' ? 'border-color: var(--warm-500); background: rgba(212,146,12,0.08);' : ''">
                                <input type="radio" x-model="form.delivery_type" value="delivery" @change="calculateDeliveryFee()" class="order-radio mr-3">
                                <span style="color: var(--warm-200);">Delivery</span>
                            </label>
                            @endif
                        </div>

                        @if ($deliveryEnabled)
                        <div x-show="form.delivery_type === 'delivery'" class="mt-4 space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Delivery Address *</label>
                                <textarea x-model="form.delivery_address" placeholder="Full address" class="order-input" rows="3"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Distance</label>
                                <select x-model="form.delivery_tier" @change="calculateDeliveryFee()" class="order-input">
                                    <option value="">Select distance</option>
                                    @foreach ($deliveryTiers as $index => $tier)
                                    <option value="{{ $index }}">{{ $tier['description'] }} (${{ number_format($tier['fee'], 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($freeDeliveryMin)
                            <p class="text-sm" style="color: var(--warm-500);">
                                🚚 Free delivery on orders over ${{ number_format((float)$freeDeliveryMin, 2) }}!
                            </p>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Date & Time --}}
                    <div class="pt-6 mt-6" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="uppercase tracking-[0.2em] text-xs font-semibold" style="color: var(--warm-500);">When</span>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">
                                    <span x-text="form.delivery_type === 'delivery' ? 'Delivery Date' : 'Pickup Date'"></span> *
                                </label>
                                <input type="date" x-model="form.delivery_date" :min="minDate" @change="checkCapacity()" required class="order-input">
                                <div x-show="capacityWarning" class="text-amber-400 text-sm mt-1" x-text="capacityWarning"></div>
                                <div x-show="capacityError" class="text-red-400 text-sm mt-1" x-text="capacityError"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: var(--warm-400);">Preferred Time</label>
                                <input type="text" x-model="form.delivery_time" placeholder="e.g., 10:00 AM" class="order-input">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="pt-6 mt-6" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <label class="block text-xs font-medium uppercase tracking-wider mb-2" style="color: var(--warm-500);">Special Instructions</label>
                        <textarea x-model="form.notes" placeholder="Allergies, decorations, anything..." class="order-input" rows="3"></textarea>
                    </div>

                    @if (!empty($paymentMethods))
                    <div class="pt-4 mt-4" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <p class="text-xs" style="color: var(--warm-600);">
                            <span class="font-medium" style="color: var(--warm-500);">Payment:</span> {{ implode(', ', array_map('ucfirst', $paymentMethods)) }}
                        </p>
                    </div>
                    @endif

                    @if ($allergyDisclaimer)
                    <div class="pt-4 mt-4" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <p class="text-xs leading-relaxed" style="color: var(--warm-600);">
                            <strong style="color: var(--warm-500);">⚠ Allergy Notice:</strong> {{ $allergyDisclaimer }}
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
<script>
function orderForm() {
    return {
        cartItems: [],
        form: {
            customer_name: '',
            customer_email: localStorage.getItem('customer_email') || '',
            customer_phone: '',
            customer_birthday: '',
            delivery_type: 'pickup',
            delivery_address: '',
            delivery_tier: '',
            delivery_date: '',
            delivery_time: '',
            notes: ''
        },
        favorites: [],
        subtotal: 0,
        deliveryFee: 0,
        discountAmount: 0,
        total: 0,
        couponCode: '',
        appliedCoupon: null,
        couponError: '',
        isApplyingCoupon: false,
        giftCardCode: '',
        appliedGiftCard: null,
        giftCardError: '',
        isApplyingGiftCard: false,
        giftCardAmount: 0,
        isSubmitting: false,
        submitError: '',
        capacityWarning: '',
        capacityError: '',
        minDate: '',
        availabilityData: [],
        unavailableDates: [],

        init() {
            const leadTimeHours = {{ $leadTimeHours }};
            const today = new Date();
            today.setTime(today.getTime() + (leadTimeHours * 60 * 60 * 1000));
            this.minDate = today.toISOString().split('T')[0];
            this.loadAvailability();
            if (this.form.customer_email) {
                this.loadFavorites();
            }
            this.calculateTotals();

            const params = new URLSearchParams(window.location.search);
            if (params.has('reorder')) {
                fetch(`/order/reorder/${params.get('reorder')}`)
                    .then(r => r.json())
                    .then(data => {
                        this.cartItems = data.items.map(item => ({
                            id: item.product_id,
                            name: item.product_name,
                            price: parseFloat(item.price),
                            quantity: item.quantity
                        }));
                        this.calculateTotals();
                    });
            }
        },

        async loadAvailability() {
            try {
                const response = await fetch('/availability');
                this.availabilityData = await response.json();
                this.unavailableDates = this.availabilityData
                    .filter(d => !d.available)
                    .map(d => d.date);
            } catch (e) {
                console.error('Failed to load availability', e);
            }
        },

        getDateAvailability(dateStr) {
            return this.availabilityData.find(d => d.date === dateStr);
        },

        get canSubmit() {
            return this.cartItems.length > 0 &&
                   this.form.customer_name &&
                   this.form.customer_email &&
                   this.form.delivery_date &&
                   (this.form.delivery_type === 'pickup' ||
                    (this.form.delivery_address && this.form.delivery_tier)) &&
                   !this.capacityError &&
                   !this.isSubmitting;
        },

        incrementItem(productId, price) {
            const existingItem = this.cartItems.find(item => item.id === productId);
            const productElement = document.querySelector(`[data-product-id="${productId}"]`);
            const productName = productElement ? productElement.dataset.productName : `Product ${productId}`;

            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.cartItems.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: 1
                });
            }
            this.calculateTotals();
        },

        decrementItem(productId) {
            const existingItem = this.cartItems.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity--;
                if (existingItem.quantity <= 0) {
                    this.cartItems = this.cartItems.filter(item => item.id !== productId);
                }
            }
            this.calculateTotals();
        },

        getQuantity(productId) {
            const item = this.cartItems.find(item => item.id === productId);
            return item ? item.quantity : 0;
        },

        calculateTotals() {
            this.subtotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            this.calculateDeliveryFee();
            this.calculateDiscount();
            let afterDiscount = Math.max(0, this.subtotal + this.deliveryFee - this.discountAmount);
            if (this.appliedGiftCard) {
                this.giftCardAmount = Math.min(this.appliedGiftCard.available_balance, afterDiscount);
            } else {
                this.giftCardAmount = 0;
            }
            this.total = Math.max(0, afterDiscount - this.giftCardAmount);
        },

        calculateDeliveryFee() {
            if (this.form.delivery_type === 'pickup') {
                this.deliveryFee = 0;
            } else {
                const tiers = @json($deliveryTiers);
                const freeMin = {{ (float)($freeDeliveryMin ?: 0) }};
                const tierIndex = parseInt(this.form.delivery_tier);
                if (!isNaN(tierIndex) && tiers[tierIndex]) {
                    this.deliveryFee = (freeMin > 0 && this.subtotal >= freeMin) ? 0 : parseFloat(tiers[tierIndex].fee);
                } else {
                    this.deliveryFee = 0;
                }
            }
            this.calculateTotals();
        },

        calculateDiscount() {
            if (this.appliedCoupon) {
                this.discountAmount = this.appliedCoupon.discount || 0;
            } else {
                this.discountAmount = 0;
            }
        },

        saveEmail() {
            localStorage.setItem('customer_email', this.form.customer_email);
            this.loadFavorites();
        },

        async loadFavorites() {
            if (!this.form.customer_email) return;
            try {
                const response = await fetch(`{{ route('favorites.get') }}?email=${encodeURIComponent(this.form.customer_email)}`);
                const data = await response.json();
                this.favorites = data.favorites || [];
            } catch (error) {
                console.error('Error loading favorites:', error);
            }
        },

        isFavorite(productId) {
            return this.favorites.includes(productId);
        },

        async toggleFavorite(productId) {
            if (!this.form.customer_email) {
                alert('Please enter your email to save favorites');
                return;
            }
            try {
                const response = await fetch('{{ route('favorites.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        customer_email: this.form.customer_email,
                        product_id: productId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    if (data.is_favorite) {
                        this.favorites.push(productId);
                    } else {
                        this.favorites = this.favorites.filter(id => id !== productId);
                    }
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        },

        async applyGiftCard() {
            if (!this.giftCardCode || this.isApplyingGiftCard) return;
            this.isApplyingGiftCard = true;
            this.giftCardError = '';
            try {
                const response = await fetch('{{ route("gift-card.apply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: this.giftCardCode,
                        subtotal: this.subtotal
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.appliedGiftCard = data;
                    this.giftCardError = '';
                    this.calculateTotals();
                } else {
                    this.giftCardError = data.error || 'Invalid gift card';
                    this.appliedGiftCard = null;
                    this.calculateTotals();
                }
            } catch (error) {
                this.giftCardError = 'Error validating gift card';
            } finally {
                this.isApplyingGiftCard = false;
            }
        },

        async applyCoupon() {
            if (!this.couponCode || this.isApplyingCoupon) return;
            this.isApplyingCoupon = true;
            this.couponError = '';
            try {
                const response = await fetch('{{ route('coupon.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: this.couponCode,
                        subtotal: this.subtotal
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.appliedCoupon = data;
                    this.couponError = '';
                    this.calculateTotals();
                } else {
                    this.couponError = data.error || 'Invalid coupon';
                    this.appliedCoupon = null;
                }
            } catch (error) {
                this.couponError = 'Error validating coupon';
                console.error('Error applying coupon:', error);
            } finally {
                this.isApplyingCoupon = false;
            }
        },

        async checkCapacity() {
            if (!this.form.delivery_date) return;
            this.capacityWarning = '';
            this.capacityError = '';

            const avail = this.getDateAvailability(this.form.delivery_date);
            if (avail && !avail.available) {
                this.capacityError = avail.reason === 'Closed'
                    ? 'The bakery is closed on this day.'
                    : (avail.reason || 'This date is not available.');
                return;
            }
            if (avail && avail.remaining_capacity > 0 && avail.remaining_capacity <= 5) {
                this.capacityWarning = `Only ${avail.remaining_capacity} order slots remaining for this date.`;
            }

            try {
                const response = await fetch(`/capacity/check/${this.form.delivery_date}`);
                const data = await response.json();
                if (!data.available) {
                    this.capacityError = 'This date is fully booked. Please choose another date.';
                } else if (data.usage_percent > 80) {
                    this.capacityWarning = `This date is ${Math.round(data.usage_percent)}% full (${data.remaining} slots remaining).`;
                }
            } catch (error) {
                console.error('Error checking capacity:', error);
            }
        },

        async submitOrder() {
            if (!this.canSubmit) return;
            this.isSubmitting = true;
            this.submitError = '';

            const formData = new FormData();
            Object.keys(this.form).forEach(key => {
                if (this.form[key]) {
                    formData.append(key, this.form[key]);
                }
            });
            this.cartItems.forEach((item, index) => {
                formData.append(`items[${index}][product_id]`, item.id);
                formData.append(`items[${index}][quantity]`, item.quantity);
            });
            if (this.appliedCoupon) {
                formData.append('coupon_id', this.appliedCoupon.coupon_id);
            }
            if (this.appliedGiftCard) {
                formData.append('gift_card_id', this.appliedGiftCard.gift_card_id);
            }
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('order.store') }}', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    window.location.href = response.url;
                } else {
                    this.submitError = 'There was an error submitting your order. Please try again.';
                }
            } catch (error) {
                this.submitError = 'There was an error submitting your order. Please try again.';
                console.error('Error submitting order:', error);
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
</x-layouts.storefront>