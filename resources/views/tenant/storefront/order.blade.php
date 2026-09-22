<x-layouts.storefront>
    <x-slot:styles>
        <style @cspnonce>
            .order-product-card {
                position: relative;
                border-radius: 20px;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                background: var(--warm-800);
            }
            .order-product-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px rgba(28, 20, 16, 0.3);
            }
            .order-product-card:hover img {
                transform: scale(1.08);
            }
            .order-product-card img {
                transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            .order-sidebar-card {
                background: var(--warm-800);
                border-radius: 20px;
                border: 1px solid rgba(212, 146, 12, 0.15);
            }
            .order-qty-btn {
                width: 2.75rem;
                height: 2.75rem;
                border-radius: 0.625rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 1rem;
                border: 1.5px solid color-mix(in srgb, var(--warm-500) 30%, transparent);
                background: color-mix(in srgb, var(--warm-500) 10%, transparent);
                color: var(--warm-400);
                cursor: pointer;
                transition: all 0.2s;
            }
            .order-qty-btn:hover:not(:disabled) {
                background: var(--warm-500);
                color: var(--warm-900);
                border-color: var(--warm-500);
            }
            .order-qty-btn:disabled {
                opacity: 0.2;
                cursor: not-allowed;
            }
            .order-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 0.75rem;
                border: 1.5px solid rgba(139, 104, 68, 0.25);
                background: rgba(255, 255, 255, 0.05);
                color: var(--warm-100);
                font-size: 0.95rem;
                transition:
                    border-color 0.2s,
                    box-shadow 0.2s;
                outline: none;
            }
            .order-input:focus {
                border-color: var(--warm-500);
                box-shadow: 0 0 0 3px rgba(212, 146, 12, 0.12);
            }
            .order-input::placeholder {
                color: var(--warm-600);
            }
            .order-input option {
                background: var(--warm-800);
                color: var(--warm-100);
            }
            .order-radio {
                accent-color: var(--warm-500);
            }
            @keyframes cartPulse {
                0%,
                100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }
        </style>
    </x-slot:styles>
    @if ($settings->orders->sitewideSaleEnabled && $settings->orders->sitewideSalePercent > 0)
        <div class="bg-warm-500 text-warm-900 px-4 py-3 text-center text-sm font-semibold md:text-base">
            🎉 {{ $settings->orders->sitewideSaleLabel }} — {{ $settings->orders->sitewideSalePercent }}% off
            everything, applied at checkout.
        </div>
    @endif
    {{-- Dark Hero Banner --}}
    <section @class(['bg-warm-900 relative overflow-hidden pt-8', 'biscotto-order-hero' => $storefrontTheme === 'biscotto'])>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_0%,color-mix(in_srgb,var(--warm-500)_8%,transparent),transparent_60%)]"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 py-16 md:py-24">
            <x-storefront.eyebrow align="left" class="mb-6">Fresh From Our Ovens</x-storefront.eyebrow>
            <h1 class="font-display text-warm-100 mb-4 text-4xl font-bold md:text-6xl">Place Your Order</h1>
            <p class="text-warm-100 max-w-2xl text-lg">
                Choose your items, tell us when you need them, and we'll have everything freshly prepared. Orders need {{ $settings->orders->leadTimeHours }} hours
                notice — ready {{ now()->addDays($settings->leadTimeDays())->format('l, F j') }} or later.
            </p>
        </div>
    </section>

    {{-- Main Content --}}
    <section @class(['bg-warm-900 relative', 'biscotto-order-stage' => $storefrontTheme === 'biscotto'])>
        <div class="relative z-10 mx-auto max-w-7xl px-4 pb-24" x-data="orderForm()" x-init="init()">
            <form data-test="order-form" @submit.prevent="submitOrder" class="grid gap-8 lg:grid-cols-3">
                <x-storefront.order-products :categories="$categories" />

                <div class="lg:col-span-1">
                    <div class="order-sidebar-card sticky top-24 p-6 md:p-8">
                        <x-storefront.order-cart-summary :content="$content" />
                        <x-storefront.order-checkout-details :settings="$settings" :content="$content" />
                    </div>
                </div>
            </form>
        </div>
    </section>
    <x-storefront.order-form-script
        :settings="$settings"
        :hydrated-cart-items="$hydratedCartItems ?? []"
        :hydrated-cart-name="$hydratedCartName ?? null"
        :hydrated-cart-email="$hydratedCartEmail ?? null"
    />
</x-layouts.storefront>
