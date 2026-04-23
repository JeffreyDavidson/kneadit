<script @cspnonce>
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
        tipPercent: 0,
        tipMode: 'preset',
        customTip: '',
        tipAmount: 0,
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
        availableSlots: [],
        pickupSlotsEnabled: {{ $settings->orders->pickupSlotsEnabled ? 'true' : 'false' }},

        init() {
            const leadTimeHours = {{ $settings->orders->leadTimeHours }};
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

        get currentMinimumOrder() {
            const pickupMin = {{ (float) ($settings->orders->minimumPickupOrderAmount ?: 0) }};
            const deliveryMin = {{ (float) ($settings->orders->minimumDeliveryOrderAmount ?: 0) }};
            return this.form.delivery_type === 'delivery' ? deliveryMin : pickupMin;
        },

        get meetsMinimumOrder() {
            return this.currentMinimumOrder <= 0 || this.subtotal >= this.currentMinimumOrder;
        },

        get amountBelowMinimum() {
            return Math.max(0, this.currentMinimumOrder - this.subtotal);
        },

        get canSubmit() {
            return this.cartItems.length > 0 &&
                   this.form.customer_name &&
                   this.form.customer_email &&
                   this.form.delivery_date &&
                   (this.form.delivery_type === 'pickup' ||
                    (this.form.delivery_address && this.form.delivery_tier)) &&
                   this.meetsMinimumOrder &&
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
            this.calculateTip();
            let afterDiscount = Math.max(0, this.subtotal + this.deliveryFee - this.discountAmount);
            if (this.appliedGiftCard) {
                this.giftCardAmount = Math.min(this.appliedGiftCard.available_balance, afterDiscount);
            } else {
                this.giftCardAmount = 0;
            }
            this.total = Math.max(0, afterDiscount - this.giftCardAmount) + this.tipAmount;
        },

        calculateTip() {
            if (this.tipMode === 'custom') {
                this.tipAmount = Math.max(0, parseFloat(this.customTip) || 0);
            } else {
                this.tipAmount = this.subtotal > 0
                    ? Math.round(this.subtotal * (this.tipPercent / 100) * 100) / 100
                    : 0;
            }
        },

        selectTipPreset(percent) {
            this.tipMode = 'preset';
            this.tipPercent = percent;
            this.customTip = '';
            this.calculateTotals();
        },

        selectCustomTip() {
            this.tipMode = 'custom';
            this.tipPercent = 0;
            this.calculateTotals();
        },

        calculateDeliveryFee() {
            if (this.form.delivery_type === 'pickup') {
                this.deliveryFee = 0;
            } else {
                const tiers = @json($settings->orders->deliveryFeeTiers);
                const freeMin = {{ (float)($settings->orders->freeDeliveryMinimum ?: 0) }};
                const tierIndex = parseInt(this.form.delivery_tier);
                if (!isNaN(tierIndex) && tiers[tierIndex]) {
                    this.deliveryFee = (freeMin > 0 && this.subtotal >= freeMin) ? 0 : parseFloat(tiers[tierIndex].fee);
                } else {
                    this.deliveryFee = 0;
                }
            }
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
                const response = await fetch(`{{ route('api.favorites.index') }}?email=${encodeURIComponent(this.form.customer_email)}`, {
                    headers: { 'Accept': 'application/vnd.api+json' },
                });
                const payload = await response.json();
                this.favorites = (payload.data || []).map(row => row.attributes.product_id);
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
                const response = await fetch('{{ route('api.favorites.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/vnd.api+json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: this.form.customer_email,
                        product_id: productId
                    })
                });
                if (!response.ok) return;
                const payload = await response.json();
                if (payload.data?.attributes?.favorited) {
                    if (!this.favorites.includes(productId)) {
                        this.favorites.push(productId);
                    }
                } else {
                    this.favorites = this.favorites.filter(id => id !== productId);
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
                const response = await fetch('{{ route("giftCard.apply") }}', {
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

        async onDateChange() {
            await this.checkCapacity();
            if (this.pickupSlotsEnabled && this.form.delivery_date) {
                await this.loadPickupSlots();
            }
        },

        async loadPickupSlots() {
            this.availableSlots = [];
            this.form.delivery_time = '';
            try {
                const response = await fetch(`/pickup-slots/${this.form.delivery_date}`);
                if (!response.ok) return;
                const payload = await response.json();
                this.availableSlots = payload.data?.slots || [];
            } catch (error) {
                console.error('Error loading pickup slots:', error);
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
            if (this.tipAmount > 0) {
                formData.append('tip_amount', this.tipAmount.toFixed(2));
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
