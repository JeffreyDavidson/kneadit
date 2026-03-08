@extends('layouts.storefront')

@section('content')
<style>
    .order-card {
        background: white;
        border-radius: 1rem;
        border: 1px solid var(--warm-200);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .order-sidebar {
        background: white;
        border-radius: 1rem;
        border: 1.5px solid var(--warm-300);
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .order-sidebar h3 {
        font-family: var(--font-display);
    }
    .order-sidebar h4 {
        font-family: var(--font-display);
    }
    .order-category-card {
        background: var(--warm-50);
        border-radius: 0.75rem;
        border: 1px solid var(--warm-200);
    }
    .order-product-card {
        border: 1px solid var(--warm-200);
        border-radius: 0.75rem;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .order-product-card:hover {
        border-color: var(--warm-400);
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .order-qty-btn {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        border: 1.5px solid var(--warm-300);
        background: white;
        color: var(--warm-700);
        cursor: pointer;
        transition: all 0.15s;
    }
    .order-qty-btn:hover:not(:disabled) {
        background: var(--warm-200);
        border-color: var(--warm-500);
    }
    .order-qty-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    .order-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-12" 
     x-data="orderForm()" 
     x-init="init()">
     
    @php
        $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
        $leadTimeDays = ceil($leadTimeHours / 24);
        $deliveryEnabled = \App\Models\Setting::get('delivery_enabled', '1') === '1';
        $deliveryTiers = json_decode(\App\Models\Setting::get('delivery_fee_tiers', '[]'), true);
        $allergyDisclaimer = \App\Models\Setting::get('allergy_disclaimer');
        $paymentMethods = json_decode(\App\Models\Setting::get('payment_methods_accepted', '[]'), true);
        $freeDeliveryMin = \App\Models\Setting::get('free_delivery_minimum', '50');
    @endphp

    {{-- Header --}}
    <div class="mb-12">
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">
            Place Your Order
        </h1>
        <p class="text-lg max-w-2xl" style="color: var(--warm-600);">
            Choose your items, tell us when you need them, and we'll have everything freshly prepared. Orders need {{ $leadTimeHours }} hours notice — ready {{ date('l, F j', strtotime('+' . $leadTimeDays . ' days')) }} or later.
        </p>
    </div>

    <form @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
        {{-- Product Selection --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center gap-4 mb-2">
                <h2 class="font-display text-2xl font-bold whitespace-nowrap" style="color: var(--warm-900);">Select Your Items</h2>
                <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
            </div>
            
            @foreach($categories as $category)
            <div class="order-category-card p-5 md:p-6">
                <h3 class="font-display text-xl font-semibold mb-5" style="color: var(--warm-900);">
                    {{ $category->name }}
                </h3>
                
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($category->products as $product)
                    @if($product->is_available)
                    <div class="order-product-card" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                        {{-- Favorite Heart --}}
                        <button type="button"
                                @click="toggleFavorite({{ $product->id }})" 
                                class="absolute top-2 right-2 text-xl z-10"
                                :class="isFavorite({{ $product->id }}) ? 'text-red-500' : 'text-warm-300 hover:text-red-400'">
                            <span x-text="isFavorite({{ $product->id }}) ? '❤️' : '🤍'"></span>
                        </button>

                        {{-- Product Image --}}
                        <div style="aspect-ratio: 16/9;">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-600), var(--warm-500));">
                                    <span class="text-3xl font-display font-bold" style="color: var(--warm-200);">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="p-4">
                            <h4 class="font-semibold mb-1" style="color: var(--warm-900);">{{ $product->name }}</h4>
                            @if($product->description)
                            <p class="text-sm mb-2" style="color: var(--warm-600);">{{ $product->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-3">
                                <span class="font-display text-lg font-bold" style="color: var(--warm-800);">${{ number_format($product->price, 2) }}</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="decrementItem({{ $product->id }})"
                                            :disabled="getQuantity({{ $product->id }}) <= 0"
                                            class="order-qty-btn">−</button>
                                    <span class="font-semibold min-w-[1.5rem] text-center text-sm" style="color: var(--warm-900);"
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
            <div class="order-sidebar p-6 md:p-8 sticky top-8">
                <h3 class="text-xl font-semibold mb-6" style="color: var(--warm-900);">Your Order</h3>
                
                {{-- Cart Items --}}
                <div class="space-y-2 mb-4" x-show="cartItems.length > 0">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="flex justify-between items-center text-sm py-1">
                            <span style="color: var(--warm-700);">
                                <span class="font-medium" x-text="item.quantity"></span> × <span x-text="item.name"></span>
                            </span>
                            <span class="font-medium" style="color: var(--warm-900);" x-text="'$' + (item.quantity * item.price).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
                
                <div x-show="cartItems.length === 0" class="text-center py-6 mb-4">
                    <p class="text-sm italic" style="color: var(--warm-400);">Add items to get started</p>
                </div>

                {{-- Coupon Section --}}
                <div class="border-t pt-4 mb-4" style="border-color: var(--warm-200);">
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Coupon Code</label>
                    <div class="flex">
                        <input type="text" 
                               x-model="couponCode"
                               placeholder="Enter coupon"
                               class="input-field rounded-r-none flex-1">
                        <button type="button" 
                                @click="applyCoupon()"
                                :disabled="!couponCode || isApplyingCoupon"
                                class="btn-secondary rounded-l-none border-l-0"
                                :class="isApplyingCoupon ? 'opacity-50 cursor-not-allowed' : ''">
                            <span x-text="isApplyingCoupon ? '...' : 'Apply'"></span>
                        </button>
                    </div>
                    <div x-show="couponError" class="text-red-600 text-sm mt-1" x-text="couponError"></div>
                    <div x-show="appliedCoupon" class="text-green-600 text-sm mt-1">
                        <span x-text="appliedCoupon?.label"></span> applied!
                    </div>
                </div>

                {{-- Gift Card Section --}}
                <div class="border-t pt-4 mb-4" style="border-color: var(--warm-200);">
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Gift Card</label>
                    <div class="flex">
                        <input type="text" 
                               x-model="giftCardCode"
                               placeholder="XXXX-XXXX-XXXX-XXXX"
                               class="input-field rounded-r-none flex-1 font-mono uppercase tracking-wider text-sm">
                        <button type="button" 
                                @click="applyGiftCard()"
                                :disabled="!giftCardCode || isApplyingGiftCard"
                                class="btn-secondary rounded-l-none border-l-0"
                                :class="isApplyingGiftCard ? 'opacity-50 cursor-not-allowed' : ''">
                            <span x-text="isApplyingGiftCard ? '...' : 'Apply'"></span>
                        </button>
                    </div>
                    <div x-show="giftCardError" class="text-red-600 text-sm mt-1" x-text="giftCardError"></div>
                    <div x-show="appliedGiftCard" class="text-green-600 text-sm mt-1">
                        Gift card applied! Balance: $<span x-text="appliedGiftCard?.available_balance?.toFixed(2)"></span>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="border-t pt-4 space-y-2 text-sm" style="border-color: var(--warm-200);">
                    <div class="order-total-row">
                        <span style="color: var(--warm-600);">Subtotal</span>
                        <span style="color: var(--warm-800);" x-text="'$' + subtotal.toFixed(2)"></span>
                    </div>
                    <div x-show="deliveryFee > 0" class="order-total-row">
                        <span style="color: var(--warm-600);">Delivery</span>
                        <span style="color: var(--warm-800);" x-text="'$' + deliveryFee.toFixed(2)"></span>
                    </div>
                    <div x-show="appliedCoupon" class="order-total-row" style="color: #16a34a;">
                        <span>Discount</span>
                        <span x-text="'-$' + discountAmount.toFixed(2)"></span>
                    </div>
                    <div x-show="appliedGiftCard" class="order-total-row" style="color: #16a34a;">
                        <span>Gift Card</span>
                        <span x-text="'-$' + giftCardAmount.toFixed(2)"></span>
                    </div>
                    <div class="order-total-row pt-3 border-t" style="border-color: var(--warm-200);">
                        <span class="font-display text-lg font-bold" style="color: var(--warm-900);">Total</span>
                        <span class="font-display text-2xl font-bold" style="color: var(--warm-900);" x-text="'$' + total.toFixed(2)"></span>
                    </div>
                </div>

                {{-- Customer Information --}}
                <div class="border-t pt-6 mt-6" style="border-color: var(--warm-200);">
                    <h4 class="text-lg font-semibold mb-4" style="color: var(--warm-900);">Your Details</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Name *</label>
                            <input type="text" x-model="form.customer_name" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Email *</label>
                            <input type="email" x-model="form.customer_email" @input="saveEmail()" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Phone</label>
                            <input type="tel" x-model="form.customer_phone" class="input-field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Birthday <span class="text-xs" style="color: var(--warm-400);">(for special treats 🎂)</span></label>
                            <input type="date" x-model="form.customer_birthday" class="input-field" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                {{-- Delivery Options --}}
                <div class="border-t pt-6 mt-6" style="border-color: var(--warm-200);">
                    <h4 class="text-lg font-semibold mb-4" style="color: var(--warm-900);">Delivery</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.delivery_type" value="pickup" @change="calculateDeliveryFee()" class="mr-3" style="accent-color: var(--warm-600);">
                            <span style="color: var(--warm-800);">Pickup <span class="text-sm" style="color: var(--warm-500);">(Free)</span></span>
                        </label>
                        
                        @if($deliveryEnabled)
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.delivery_type" value="delivery" @change="calculateDeliveryFee()" class="mr-3" style="accent-color: var(--warm-600);">
                            <span style="color: var(--warm-800);">Delivery</span>
                        </label>
                        @endif
                    </div>
                    
                    @if($deliveryEnabled)
                    <div x-show="form.delivery_type === 'delivery'" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Delivery Address *</label>
                            <textarea x-model="form.delivery_address" placeholder="Full address" class="input-field" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Distance</label>
                            <select x-model="form.delivery_tier" @change="calculateDeliveryFee()" class="input-field">
                                <option value="">Select distance</option>
                                @foreach($deliveryTiers as $index => $tier)
                                <option value="{{ $index }}">{{ $tier['description'] }} (${{ number_format($tier['fee'], 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        @if($freeDeliveryMin)
                        <p class="text-sm" style="color: var(--warm-500);">
                            Free delivery on orders over ${{ number_format((float)$freeDeliveryMin, 2) }}!
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Date & Time --}}
                <div class="border-t pt-6 mt-6" style="border-color: var(--warm-200);">
                    <h4 class="text-lg font-semibold mb-4" style="color: var(--warm-900);">When</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">
                                <span x-text="form.delivery_type === 'delivery' ? 'Delivery Date' : 'Pickup Date'"></span> *
                            </label>
                            <input type="date" x-model="form.delivery_date" :min="minDate" @change="checkCapacity()" required class="input-field">
                            <div x-show="capacityWarning" class="text-orange-600 text-sm mt-1" x-text="capacityWarning"></div>
                            <div x-show="capacityError" class="text-red-600 text-sm mt-1" x-text="capacityError"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Preferred Time</label>
                            <input type="text" x-model="form.delivery_time" placeholder="e.g., 10:00 AM" class="input-field">
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="border-t pt-6 mt-6" style="border-color: var(--warm-200);">
                    <label class="block text-sm font-medium mb-2" style="color: var(--warm-800);">Special Instructions</label>
                    <textarea x-model="form.notes" placeholder="Allergies, decorations, anything..." class="input-field" rows="3"></textarea>
                </div>

                @if(!empty($paymentMethods))
                <div class="border-t pt-4 mt-4" style="border-color: var(--warm-200);">
                    <p class="text-xs" style="color: var(--warm-500);">
                        <span class="font-medium">Payment:</span> {{ implode(', ', array_map('ucfirst', $paymentMethods)) }}
                    </p>
                </div>
                @endif

                @if($allergyDisclaimer)
                <div class="border-t pt-4 mt-4" style="border-color: var(--warm-200);">
                    <p class="text-xs leading-relaxed" style="color: var(--warm-500);">
                        <strong>⚠ Allergy Notice:</strong> {{ $allergyDisclaimer }}
                    </p>
                </div>
                @endif

                {{-- Submit --}}
                <button type="submit" 
                        :disabled="!canSubmit || isSubmitting"
                        class="w-full mt-6 py-4 rounded-xl text-lg font-semibold transition-all duration-200"
                        :class="!canSubmit || isSubmitting ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-90'"
                        style="background: var(--warm-800); color: white; font-family: var(--font-display);">
                    <span x-text="isSubmitting ? 'Placing Order...' : 'Place Order'"></span>
                </button>
                
                <div x-show="submitError" class="text-red-600 text-sm mt-2 text-center" x-text="submitError"></div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
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
@endsection