@extends('layouts.storefront')

@section('content')
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

    <div class="text-center mb-12">
        <h1 class="font-display text-4xl font-bold text-warm-900 mb-4">
            Place Your Order
        </h1>
        <p class="text-warm-700 text-lg">
            Please allow {{ $leadTimeHours }} hours notice for all orders. Orders placed today will be ready {{ date('l, F j', strtotime('+' . $leadTimeDays . ' days')) }} or later.
        </p>
    </div>

    <form @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
        <!-- Product Selection -->
        <div class="lg:col-span-2">
            <h2 class="font-display text-2xl font-semibold text-warm-900 mb-6">Select Your Items</h2>
            
            @foreach($categories as $category)
            <div class="card p-6 mb-6">
                <h3 class="font-display text-xl font-semibold text-warm-900 mb-4">
                    {{ $category->name }}
                </h3>
                
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($category->products as $product)
                    @if($product->is_available)
                    <div class="border border-warm-200 rounded-lg p-4 relative">
                        <!-- Favorite Heart -->
                        <button type="button"
                                @click="toggleFavorite({{ $product->id }})" 
                                class="absolute top-2 right-2 text-xl"
                                :class="isFavorite({{ $product->id }}) ? 'text-red-500' : 'text-warm-300 hover:text-red-400'">
                            <span x-text="isFavorite({{ $product->id }}) ? '❤️' : '🤍'"></span>
                        </button>
                        
                        <div class="pr-6">
                            <h4 class="font-semibold text-warm-900 mb-1">{{ $product->name }}</h4>
                            @if($product->description)
                            <p class="text-sm text-warm-700 mb-2">{{ $product->description }}</p>
                            @endif
                            <p class="font-bold text-warm-600">${{ number_format($product->price, 2) }}</p>
                            
                            <div class="flex items-center mt-3">
                                <button type="button" 
                                        @click="decrementItem({{ $product->id }})"
                                        :disabled="getQuantity({{ $product->id }}) <= 0"
                                        class="btn-secondary py-1 px-3 text-sm"
                                        :class="getQuantity({{ $product->id }}) <= 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    −
                                </button>
                                <span class="mx-3 font-semibold min-w-[2rem] text-center" 
                                      x-text="getQuantity({{ $product->id }})"></span>
                                <button type="button" 
                                        @click="incrementItem({{ $product->id }}, {{ $product->price }})"
                                        class="btn-secondary py-1 px-3 text-sm">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- Order Summary & Customer Info -->
        <div class="lg:col-span-1">
            <div class="card p-6 sticky top-8">
                <h3 class="font-display text-xl font-semibold text-warm-900 mb-4">Order Summary</h3>
                
                <!-- Cart Items -->
                <div class="space-y-2 mb-4" x-show="cartItems.length > 0">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="flex justify-between items-center text-sm">
                            <span>
                                <span x-text="item.quantity"></span>× <span x-text="item.name"></span>
                            </span>
                            <span x-text="'$' + (item.quantity * item.price).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
                
                <div x-show="cartItems.length === 0" class="text-gray-500 italic text-sm mb-4">
                    No items selected
                </div>

                <!-- Coupon Section -->
                <div class="border-t border-warm-200 pt-4 mb-4">
                    <label class="block text-sm font-medium text-warm-900 mb-2">Coupon Code</label>
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
                            <span x-text="isApplyingCoupon ? 'Checking...' : 'Apply'"></span>
                        </button>
                    </div>
                    
                    <div x-show="couponError" class="text-red-600 text-sm mt-1" x-text="couponError"></div>
                    <div x-show="appliedCoupon" class="text-green-600 text-sm mt-1">
                        <span x-text="appliedCoupon?.label"></span> applied!
                    </div>
                </div>

                <!-- Order Totals -->
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span x-text="'$' + subtotal.toFixed(2)"></span>
                    </div>
                    
                    <div x-show="deliveryFee > 0" class="flex justify-between">
                        <span>Delivery Fee:</span>
                        <span x-text="'$' + deliveryFee.toFixed(2)"></span>
                    </div>
                    
                    <div x-show="appliedCoupon" class="flex justify-between text-green-600">
                        <span>Discount:</span>
                        <span x-text="'-$' + discountAmount.toFixed(2)"></span>
                    </div>
                    
                    <div class="flex justify-between font-bold text-lg border-t border-warm-200 pt-2">
                        <span>Total:</span>
                        <span x-text="'$' + total.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="border-t border-warm-200 pt-6 mt-6">
                    <h4 class="font-semibold text-warm-900 mb-4">Customer Information</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Name *</label>
                            <input type="text" 
                                   x-model="form.customer_name" 
                                   required
                                   class="input-field">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Email *</label>
                            <input type="email" 
                                   x-model="form.customer_email"
                                   @input="saveEmail()"
                                   required
                                   class="input-field">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Phone</label>
                            <input type="tel" 
                                   x-model="form.customer_phone"
                                   class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Delivery Options -->
                <div class="border-t border-warm-200 pt-6 mt-6">
                    <h4 class="font-semibold text-warm-900 mb-4">Delivery Options</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" 
                                   x-model="form.delivery_type" 
                                   value="pickup"
                                   @change="calculateDeliveryFee()"
                                   class="mr-2">
                            <span>Pickup (Free)</span>
                        </label>
                        
                        @if($deliveryEnabled)
                        <label class="flex items-center">
                            <input type="radio" 
                                   x-model="form.delivery_type" 
                                   value="delivery"
                                   @change="calculateDeliveryFee()"
                                   class="mr-2">
                            <span>Delivery</span>
                        </label>
                        @endif
                    </div>
                    
                    @if($deliveryEnabled)
                    <div x-show="form.delivery_type === 'delivery'" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Delivery Address *</label>
                            <textarea x-model="form.delivery_address"
                                     placeholder="Enter your full address"
                                     class="input-field"
                                     rows="3"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Distance from bakery</label>
                            <select x-model="form.delivery_tier" 
                                    @change="calculateDeliveryFee()"
                                    class="input-field">
                                <option value="">Select distance</option>
                                @foreach($deliveryTiers as $index => $tier)
                                <option value="{{ $index }}">{{ $tier['description'] }} (${{ number_format($tier['fee'], 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        @if($freeDeliveryMin)
                        <p class="text-sm" style="color: var(--warm-600);">
                            Free delivery on orders over ${{ number_format((float)$freeDeliveryMin, 2) }}!
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Delivery Date & Time -->
                <div class="border-t border-warm-200 pt-6 mt-6">
                    <h4 class="font-semibold text-warm-900 mb-4">Date & Time</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">
                                <span x-text="form.delivery_type === 'delivery' ? 'Delivery Date' : 'Pickup Date'"></span> *
                            </label>
                            <input type="date" 
                                   x-model="form.delivery_date"
                                   :min="minDate"
                                   @change="checkCapacity()"
                                   required
                                   class="input-field">
                            
                            <div x-show="capacityWarning" class="text-orange-600 text-sm mt-1" x-text="capacityWarning"></div>
                            <div x-show="capacityError" class="text-red-600 text-sm mt-1" x-text="capacityError"></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">
                                Preferred Time
                            </label>
                            <input type="text" 
                                   x-model="form.delivery_time"
                                   placeholder="e.g., 10:00 AM or Morning"
                                   class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="border-t border-warm-200 pt-6 mt-6">
                    <label class="block text-sm font-medium text-warm-900 mb-2">Special Instructions</label>
                    <textarea x-model="form.notes"
                             placeholder="Any special requests or notes..."
                             class="input-field"
                             rows="3"></textarea>
                </div>

                @if(!empty($paymentMethods))
                <!-- Payment Methods -->
                <div class="border-t border-warm-200 pt-6 mt-6">
                    <h4 class="font-semibold text-warm-900 mb-2">Accepted Payment Methods</h4>
                    <p class="text-sm" style="color: var(--warm-700);">
                        {{ implode(', ', array_map('ucfirst', $paymentMethods)) }}
                    </p>
                </div>
                @endif

                @if($allergyDisclaimer)
                <!-- Allergy Disclaimer -->
                <div class="border-t border-warm-200 pt-4 mt-4">
                    <p class="text-xs leading-relaxed" style="color: var(--warm-600);">
                        <strong>⚠ Allergy Notice:</strong> {{ $allergyDisclaimer }}
                    </p>
                </div>
                @endif

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="!canSubmit || isSubmitting"
                        class="w-full btn-primary mt-6 py-3"
                        :class="!canSubmit || isSubmitting ? 'opacity-50 cursor-not-allowed' : ''">
                    <span x-text="isSubmitting ? 'Placing Order...' : 'Place Order'"></span>
                </button>
                
                <div x-show="submitError" class="text-red-600 text-sm mt-2" x-text="submitError"></div>
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
        isSubmitting: false,
        submitError: '',
        capacityWarning: '',
        capacityError: '',
        minDate: '',

        init() {
            // Set minimum date based on lead time hours
            const leadTimeHours = {{ $leadTimeHours }};
            const today = new Date();
            today.setTime(today.getTime() + (leadTimeHours * 60 * 60 * 1000));
            this.minDate = today.toISOString().split('T')[0];
            
            // Load favorites if email exists
            if (this.form.customer_email) {
                this.loadFavorites();
            }
            
            this.calculateTotals();
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
            this.total = Math.max(0, this.subtotal + this.deliveryFee - this.discountAmount);
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
            
            // Add form fields
            Object.keys(this.form).forEach(key => {
                if (this.form[key]) {
                    formData.append(key, this.form[key]);
                }
            });
            
            // Add cart items
            this.cartItems.forEach((item, index) => {
                formData.append(`items[${index}][product_id]`, item.id);
                formData.append(`items[${index}][quantity]`, item.quantity);
            });
            
            // Add coupon if applied
            if (this.appliedCoupon) {
                formData.append('coupon_id', this.appliedCoupon.coupon_id);
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
                    const text = await response.text();
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

// Add product data attributes for cart functionality
document.addEventListener('DOMContentLoaded', function() {
    @foreach($categories as $category)
        @foreach($category->products as $product)
        @if($product->is_available)
            const productEl{{ $product->id }} = document.querySelector('[data-product-id="{{ $product->id }}"]');
            if (!productEl{{ $product->id }}) {
                const div = document.createElement('div');
                div.setAttribute('data-product-id', '{{ $product->id }}');
                div.setAttribute('data-product-name', '{{ addslashes($product->name) }}');
                div.style.display = 'none';
                document.body.appendChild(div);
            }
        @endif
        @endforeach
    @endforeach
});
</script>
@endsection