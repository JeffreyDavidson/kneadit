@props(['settings', 'content'])

{{-- Customer Information --}}
<div class="border-warm-700/20 mt-6 border-t pt-6">
    <div class="mb-4 flex items-center gap-2">
        <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">Your Details</span>
    </div>

    <div class="space-y-3">
        <div>
            <label class="text-warm-400 mb-1 block text-xs font-medium">Name *</label>
            <input
                type="text"
                data-test="order-form-customer-name"
                x-model="form.customer_name"
                required
                class="order-input"
            />
        </div>
        <div>
            <label class="text-warm-400 mb-1 block text-xs font-medium">Email *</label>
            <input
                type="email"
                data-test="order-form-customer-email"
                x-model="form.customer_email"
                @input="saveEmail()"
                required
                class="order-input"
            />
        </div>
        <div>
            <label class="text-warm-400 mb-1 block text-xs font-medium">Phone</label>
            <input type="tel" data-test="order-form-customer-phone" x-model="form.customer_phone" class="order-input" />
        </div>
        <div>
            <label class="text-warm-400 mb-1 block text-xs font-medium">Birthday <span class="text-warm-300">(for special treats 🎂)</span></label>
            <input
                type="date"
                data-test="order-form-customer-birthday"
                x-model="form.customer_birthday"
                class="order-input"
                max="{{ date('Y-m-d') }}"
            />
        </div>
    </div>
</div>

{{-- Delivery Options --}}
<div class="border-warm-700/20 mt-6 border-t pt-6">
    <div class="mb-4 flex items-center gap-2">
        <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">Delivery</span>
    </div>

    <div class="space-y-3">
        <label
            class="border-warm-600/15 flex cursor-pointer items-center rounded-xl border bg-white/[0.03] px-4 py-3 transition-all"
            :class="form.delivery_type === 'pickup' ? 'border-warm-500 bg-warm-500/[0.08]' : ''"
        >
            <input
                type="radio"
                data-test="order-form-delivery-type-pickup"
                x-model="form.delivery_type"
                value="pickup"
                @change="calculateTotals()"
                class="order-radio mr-3"
            />
            <span class="text-warm-200">Pickup <span class="text-warm-500 text-sm">(Free)</span></span>
        </label>

        @if ($settings->orders->deliveryEnabled)
            <label
                class="border-warm-600/15 flex cursor-pointer items-center rounded-xl border bg-white/[0.03] px-4 py-3 transition-all"
                :class="form.delivery_type === 'delivery' ? 'border-warm-500 bg-warm-500/[0.08]' : ''"
            >
                <input
                    type="radio"
                    data-test="order-form-delivery-type-delivery"
                    x-model="form.delivery_type"
                    value="delivery"
                    @change="calculateTotals()"
                    class="order-radio mr-3"
                />
                <span class="text-warm-200">Delivery</span>
            </label>
        @endif
    </div>

    @if ($settings->orders->deliveryEnabled)
        <div x-show="form.delivery_type === 'delivery'" class="mt-4 space-y-3">
            <div>
                <label class="text-warm-400 mb-1 block text-xs font-medium">Delivery Address *</label>
                <textarea
                    data-test="order-form-delivery-address"
                    x-model="form.delivery_address"
                    placeholder="Full address"
                    class="order-input"
                    rows="3"
                ></textarea>
            </div>
            <div>
                <label class="text-warm-400 mb-1 block text-xs font-medium">Distance</label>
                <select
                    data-test="order-form-delivery-tier"
                    x-model="form.delivery_tier"
                    @change="calculateTotals()"
                    class="order-input"
                >
                    <option value="">Select distance</option>
                    @foreach ($settings->orders->deliveryFeeTiers as $index => $tier)
                        <option value="{{ $index }}">
                            {{ $tier['description'] }} (
                            @money($tier['fee'])
                            )
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($settings->orders->freeDeliveryMinimum)
                <p class="text-warm-500 text-sm">
                    🚚 Free delivery on orders over
                    @money((float) $settings->orders->freeDeliveryMinimum)
                    !
                </p>
            @endif
        </div>
    @endif
</div>

{{-- Date & Time --}}
<div class="border-warm-700/20 mt-6 border-t pt-6">
    <div class="mb-4 flex items-center gap-2">
        <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">When</span>
    </div>
    <div class="space-y-3">
        <div>
            <label class="text-warm-400 mb-1 block text-xs font-medium">
                <span x-text="form.delivery_type === 'delivery' ? 'Delivery Date' : 'Pickup Date'"></span>
                *
            </label>
            <input
                type="date"
                data-test="order-form-delivery-date"
                x-model="form.delivery_date"
                :min="minDate"
                @change="onDateChange()"
                required
                class="order-input"
            />
            <div x-show="capacityWarning" class="mt-1 text-sm text-amber-400" x-text="capacityWarning"></div>
            <div x-show="capacityError" class="mt-1 text-sm text-red-400" x-text="capacityError"></div>
        </div>
        <div>
            @if ($settings->orders->pickupSlotsEnabled)
                <label for="order-pickup-slot" class="text-warm-400 mb-1 block text-xs font-medium">
                    <span x-text="form.delivery_type === 'pickup' ? 'Pickup Time' : 'Preferred Time'"></span>
                </label>
                <template x-if="form.delivery_type === 'pickup'">
                    <select
                        id="order-pickup-slot"
                        data-test="order-form-delivery-time"
                        x-model="form.delivery_time"
                        class="order-input"
                    >
                        <option value="">{{ '— Select a time —' }}</option>
                        <template x-for="slot in availableSlots" :key="slot">
                            <option :value="slot" x-text="slot"></option>
                        </template>
                    </select>
                </template>
                <template x-if="form.delivery_type !== 'pickup'">
                    <input
                        type="text"
                        data-test="order-form-delivery-time"
                        x-model="form.delivery_time"
                        placeholder="e.g., 10:00 AM"
                        class="order-input"
                    />
                </template>
                <div
                    x-show="form.delivery_type === 'pickup' && availableSlots.length === 0 && form.delivery_date"
                    class="mt-1 text-sm text-amber-400"
                >
                    No pickup slots available for this date.
                </div>
            @else
                <label for="order-delivery-time" class="text-warm-400 mb-1 block text-xs font-medium"
                    >Preferred Time</label>
                <input
                    id="order-delivery-time"
                    type="text"
                    data-test="order-form-delivery-time"
                    x-model="form.delivery_time"
                    placeholder="e.g., 10:00 AM"
                    class="order-input"
                />
            @endif
        </div>
    </div>
</div>

{{-- Pickup contact (when someone else is picking up) --}}
<div x-show="form.delivery_type === 'pickup'" class="border-warm-700/20 mt-6 border-t pt-6">
    <label class="flex cursor-pointer items-center gap-2">
        <input type="checkbox" x-model="hasPickupContact" class="h-4 w-4" />
        <span class="text-warm-300 text-sm font-medium">Someone else is picking up this order</span>
    </label>
    <div x-show="hasPickupContact" class="mt-3 space-y-3">
        <div>
            <label for="pickup-contact-name" class="text-warm-400 mb-1 block text-xs font-medium">Name *</label>
            <input
                id="pickup-contact-name"
                type="text"
                x-model="form.pickup_contact_name"
                :required="hasPickupContact"
                placeholder="Their name"
                class="order-input"
            />
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label for="pickup-contact-phone" class="text-warm-400 mb-1 block text-xs font-medium">Phone</label>
                <input
                    id="pickup-contact-phone"
                    type="tel"
                    x-model="form.pickup_contact_phone"
                    placeholder="555-0123"
                    class="order-input"
                />
            </div>
            <div>
                <label for="pickup-contact-email" class="text-warm-400 mb-1 block text-xs font-medium">Email</label>
                <input
                    id="pickup-contact-email"
                    type="email"
                    x-model="form.pickup_contact_email"
                    placeholder="them@example.com"
                    class="order-input"
                />
            </div>
        </div>
        <p class="text-warm-500 text-xs">
            If you provide their email, we'll send them the pickup-ready notification too.
        </p>
    </div>
</div>

{{-- Notes --}}
<div class="border-warm-700/20 mt-6 border-t pt-6">
    <label class="text-warm-500 mb-2 block text-xs font-medium tracking-wider uppercase">Special Instructions</label>
    <textarea
        data-test="order-form-notes"
        x-model="form.notes"
        placeholder="Allergies, decorations, anything..."
        class="order-input"
        rows="3"
    ></textarea>
</div>

@if (! empty($settings->payment->methodsAccepted))
    <div class="border-warm-700/20 mt-4 border-t pt-4">
        <p class="text-warm-300 text-xs">
            <span class="text-warm-500 font-medium">Payment:</span>
            {{ implode(', ', array_map('ucfirst', $settings->payment->methodsAccepted)) }}
        </p>
    </div>
@endif

@if ($settings->branding->allergyDisclaimer)
    <div class="border-warm-700/20 mt-4 border-t pt-4">
        <p class="text-warm-300 text-xs leading-relaxed">
            <strong class="text-warm-500">⚠ Allergy Notice:</strong>
            {{ $settings->branding->allergyDisclaimer }}
        </p>
    </div>
@endif

{{-- Minimum order notice --}}
<div
    x-show="cartItems.length > 0 && ! meetsMinimumOrder"
    class="mt-6 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-300"
>
    <span
        x-text="
            `Minimum ${form.delivery_type === 'delivery' ? 'delivery' : 'pickup'} order is $${currentMinimumOrder.toFixed(2)}. Add $${amountBelowMinimum.toFixed(2)} more to continue.`
        "
    ></span>
</div>

{{-- Submit --}}
<x-storefront.button
    type="submit"
    size="lg"
    fullWidth
    fontDisplay
    data-test="order-form-submit"
    x-bind:disabled="! canSubmit || isSubmitting"
    class="mt-6"
    x-bind:class="! canSubmit || isSubmitting ? 'opacity-30 cursor-not-allowed' : ''"
>
    <span x-text="isSubmitting ? 'Placing Order...' : {{ Js::from($content['place_order_button'] ?? 'Place Order →') }}"></span>
</x-storefront.button>

<div x-show="submitError" class="mt-3 text-center text-sm text-red-400" x-text="submitError"></div>
