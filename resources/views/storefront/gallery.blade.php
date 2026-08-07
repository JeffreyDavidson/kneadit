<x-layouts.storefront>
    <div x-data="giftCardPage()">
        {{-- Photo-Forward Hero --}}
        <x-storefront.hero-section
            :image="$settings->giftCardsHeroImageUrl()"
            image-alt="Fresh baked goods"
            image-class="hero-img"
        >
            <div class="relative z-10 mx-auto flex min-h-[55vh] max-w-4xl flex-col justify-end px-4 pb-20 text-center">
                <x-storefront.eyebrow line-opacity="0.4" class="hero-fade-1 mb-6">
                    {{ $content['hero_eyebrow'] ?? 'A Sweet Gesture' }}</x-storefront.eyebrow>
                <h1 class="hero-fade-2 font-display mb-6 text-4xl leading-tight font-bold text-white md:text-6xl">
                    {!! nl2br(e($content['hero_title'] ?? "Give the Gift of\nFresh Baked Goods")) !!}
                </h1>
                <p class="hero-fade-3 font-script text-warm-400 text-2xl md:text-3xl">
                    {{ $content['hero_subtitle'] ?? 'A treat they\'ll remember long after the last crumb' }}
                </p>
            </div>
        </x-storefront.hero-section>

        {{-- Success State --}}
        <div x-show="purchasedCard" x-cloak class="bg-warm-50 px-4 py-20">
            <div class="mx-auto max-w-lg text-center">
                {{-- Gift card mockup --}}
                <div class="from-warm-900 to-warm-800 relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-br p-8 shadow-2xl">
                    <div class="bg-warm-500 absolute top-0 right-0 h-40 w-40 translate-x-[30%] -translate-y-[30%] rounded-full opacity-10"></div>
                    <div class="bg-warm-500 absolute bottom-0 left-0 h-32 w-32 -translate-x-[30%] translate-y-[30%] rounded-full opacity-10"></div>
                    <div class="relative z-10">
                        <p class="font-script text-warm-500 mb-1 text-xl">Gift Card</p>
                        <p
                            class="font-display mb-4 text-4xl font-bold text-white"
                            x-text="'$' + parseFloat(purchasedCard?.balance || 0).toFixed(2)"
                        ></p>
                        <div class="border-warm-400/20 mb-2 inline-block rounded-xl border bg-white/5 px-6 py-3">
                            <span
                                class="text-warm-400 font-mono text-lg font-bold tracking-[0.2em]"
                                x-text="purchasedCard?.code"
                            ></span>
                        </div>
                    </div>
                </div>

                <h2 class="font-display text-warm-900 mb-2 text-2xl font-bold">
                    {{ $content['success_heading'] ?? 'Gift Card Purchased!' }}
                </h2>
                <p class="text-warm-600 mb-6">
                    {{ $content['success_description'] ?? 'Share the code below with the lucky recipient.' }}
                </p>

                <div class="flex justify-center gap-4">
                    <x-storefront.button variant="outline-light" size="md" class="gap-2" @click="copyCode()">
                        <x-heroicon-o-document-duplicate class="h-4 w-4" stroke-width="2" />
                        <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                    </x-storefront.button>
                    <x-storefront.button size="md" @click="purchasedCard = null">
                        Purchase Another
                    </x-storefront.button>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div x-show="! purchasedCard">
            <section class="bg-warm-50 px-4 py-20">
                <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-5">
                    {{-- Left: Gift Card Preview + Amount Selection (3 cols) --}}
                    <div class="space-y-10 lg:col-span-3">
                        {{-- Card Preview --}}
                        <div>
                            <p class="text-warm-500 mb-4 text-xs font-semibold tracking-[0.25em] uppercase">
                                {{ $content['preview_label'] ?? 'Preview' }}
                            </p>
                            <div class="from-warm-900 to-warm-800 relative flex aspect-[16/9] flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br p-10 shadow-xl">
                                <div class="bg-warm-500 absolute top-0 right-0 h-60 w-60 translate-x-[30%] -translate-y-[30%] rounded-full opacity-[0.06]"></div>
                                <div class="bg-warm-500 absolute bottom-0 left-0 h-48 w-48 -translate-x-[30%] translate-y-[30%] rounded-full opacity-[0.06]"></div>
                                <div class="relative z-10">
                                    <p class="font-script text-warm-500 text-2xl">Gift Card</p>
                                    <p class="font-display text-warm-400 mt-1 text-lg">{{ $settings->store->name }}</p>
                                </div>
                                <div class="relative z-10 text-right">
                                    <p
                                        class="font-display text-5xl font-bold text-white"
                                        x-text="'$' + parseFloat(form.initial_balance || 0).toFixed(2)"
                                    ></p>
                                </div>
                            </div>
                        </div>

                        {{-- Amount Selection --}}
                        <div>
                            <p class="text-warm-500 mb-4 text-xs font-semibold tracking-[0.25em] uppercase">
                                {{ $content['amount_label'] ?? 'Select Amount' }}
                            </p>
                            <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <template x-for="preset in @json($settings->giftCards->presetAmounts)" :key="preset">
                                    <button
                                        type="button"
                                        @click="
                                            form.initial_balance = preset;
                                            customAmount = '';
                                        "
                                        :class="form.initial_balance == preset && ! customAmount
                                            ? 'bg-warm-500 text-warm-900 border-warm-500'
                                            : 'bg-white text-warm-800 border-warm-200'"
                                        class="font-display cursor-pointer rounded-2xl border-2 py-5 text-center text-2xl font-bold transition-all duration-300 hover:scale-105 hover:shadow-lg"
                                    >
                                        <span x-text="'$' + preset"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-warm-600 text-sm font-semibold whitespace-nowrap">Custom amount:</span>
                                <div class="relative flex-1">
                                    <span class="text-warm-500 absolute top-1/2 left-4 -translate-y-1/2 font-semibold">$</span>
                                    <input
                                        type="number"
                                        x-model="customAmount"
                                        @input="form.initial_balance = customAmount"
                                        min="1"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="border-warm-200 text-warm-800 w-full rounded-xl border-2 bg-white py-3 pr-4 pl-8 font-semibold transition-colors focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Check Balance --}}
                        <div class="border-warm-200 rounded-2xl border bg-white p-8">
                            <h3 class="font-display text-warm-900 mb-4 text-xl font-semibold">
                                {{ $content['balance_heading'] ?? 'Check Gift Card Balance' }}
                            </h3>
                            <form
                                @submit.prevent="checkBalance()"
                                class="flex flex-col gap-3 sm:flex-row"
                                data-test="gift-card-balance-form"
                            >
                                <input
                                    type="text"
                                    x-model="balanceCode"
                                    required
                                    placeholder="XXXX-XXXX-XXXX-XXXX"
                                    class="input-field flex-1 font-mono tracking-wider uppercase"
                                    data-test="gift-card-balance-form-code"
                                />
                                <x-storefront.button
                                    type="submit"
                                    variant="outline-light"
                                    size="md"
                                    x-bind:disabled="! balanceCode || isCheckingBalance"
                                    class="whitespace-nowrap"
                                    x-bind:class="isCheckingBalance ? 'opacity-50 cursor-not-allowed' : ''"
                                    data-test="gift-card-balance-form-submit"
                                >
                                    <span x-text="isCheckingBalance ? 'Checking...' : {{ Js::from($content['check_balance_button'] ?? 'Check Balance') }}"></span>
                                </x-storefront.button>
                            </form>
                            <div
                                x-show="balanceError"
                                class="mt-2 text-sm text-red-600"
                                x-text="balanceError"
                                data-test="gift-card-balance-error"
                            ></div>
                            <div x-show="balanceResult" x-cloak class="bg-warm-50 mt-6 rounded-xl p-6 text-center">
                                <p class="text-warm-500 mb-1 text-sm tracking-wider uppercase">Current Balance</p>
                                <p
                                    class="font-display text-warm-900 text-4xl font-bold"
                                    x-text="'$' + parseFloat(balanceResult?.current_balance || 0).toFixed(2)"
                                ></p>
                                <p class="text-warm-500 mt-2 text-sm" x-show="balanceResult?.expires_at">
                                    Expires: <span x-text="balanceResult?.expires_at"></span>
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-red-600"
                                    x-show="balanceResult && ! balanceResult.is_usable"
                                >
                                    This card is no longer active.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Purchase Form (2 cols) --}}
                    <div class="lg:col-span-2">
                        <div class="border-warm-200 sticky top-8 rounded-2xl border bg-white p-8 shadow-2xl">
                            <p class="text-warm-500 mb-1 text-xs font-semibold tracking-[0.25em] uppercase">
                                {{ $content['details_eyebrow'] ?? 'Details' }}
                            </p>
                            <h2 class="font-display text-warm-900 mb-6 text-2xl font-bold">
                                {{ $content['details_heading'] ?? 'Send Your Gift' }}
                            </h2>

                            <form @submit.prevent="purchase()" class="space-y-5" data-test="gift-card-purchase-form">
                                <div>
                                    <label class="text-warm-700 mb-1 block text-sm font-semibold">Your Name *</label>
                                    <input
                                        type="text"
                                        x-model="form.purchaser_name"
                                        required
                                        class="input-field"
                                        data-test="gift-card-purchase-form-purchaser-name"
                                    />
                                </div>
                                <div>
                                    <label class="text-warm-700 mb-1 block text-sm font-semibold">Your Email *</label>
                                    <input
                                        type="email"
                                        x-model="form.purchaser_email"
                                        required
                                        class="input-field"
                                        data-test="gift-card-purchase-form-purchaser-email"
                                    />
                                </div>

                                <div class="border-warm-200 border-t pt-4">
                                    <p class="font-script text-warm-500 mb-3 text-lg">
                                        {{ $content['recipient_label'] ?? 'Recipient Info' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-warm-700 mb-1 block text-sm font-semibold">Recipient Name</label>
                                    <input
                                        type="text"
                                        x-model="form.recipient_name"
                                        class="input-field"
                                        placeholder="Optional"
                                        data-test="gift-card-purchase-form-recipient-name"
                                    />
                                </div>
                                <div>
                                    <label class="text-warm-700 mb-1 block text-sm font-semibold">Recipient Email</label>
                                    <input
                                        type="email"
                                        x-model="form.recipient_email"
                                        class="input-field"
                                        placeholder="Optional"
                                        data-test="gift-card-purchase-form-recipient-email"
                                    />
                                </div>
                                <div>
                                    <label class="text-warm-700 mb-1 block text-sm font-semibold">Gift Message</label>
                                    <textarea
                                        x-model="form.message"
                                        class="input-field"
                                        rows="3"
                                        placeholder="Add a personal touch..."
                                        data-test="gift-card-purchase-form-message"
                                    ></textarea>
                                </div>

                                <div
                                    x-show="purchaseError"
                                    class="text-sm text-red-600"
                                    x-text="purchaseError"
                                    data-test="gift-card-purchase-error"
                                ></div>

                                <button
                                    type="submit"
                                    :disabled="! form.purchaser_name ||
                                    ! form.purchaser_email ||
                                    ! form.initial_balance ||
                                    isPurchasing"
                                    class="w-full rounded-full py-4 text-lg font-bold transition-all duration-300 hover:scale-105 hover:shadow-xl"
                                    :class="isPurchasing ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="bg-warm-500 text-warm-900"
                                    data-test="gift-card-purchase-form-submit"
                                >
                                    <span
                                        x-text="
                                            isPurchasing
                                                ? 'Processing...'
                                                : 'Purchase — $' + parseFloat(form.initial_balance || 0).toFixed(2)
                                        "
                                    ></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script @cspnonce>
        function giftCardPage() {
            return {
                form: {
                    purchaser_name: '',
                    purchaser_email: localStorage.getItem('customer_email') || '',
                    recipient_name: '',
                    recipient_email: '',
                    message: '',
                    initial_balance: __PINT_BLADE_0__,
                },
                customAmount: '',
                isPurchasing: false,
                purchaseError: '',
                purchasedCard: null,
                copied: false,
                balanceCode: '',
                isCheckingBalance: false,
                balanceError: '',
                balanceResult: null,

                async purchase() {
                    if (this.isPurchasing) return;
                    this.isPurchasing = true;
                    this.purchaseError = '';

                    try {
                        const response = await fetch(__PINT_BLADE_1__, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': __PINT_BLADE_2__,
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.purchasedCard = data.gift_card;
                        } else {
                            this.purchaseError = data.error || 'Something went wrong.';
                        }
                    } catch (e) {
                        this.purchaseError = 'Something went wrong. Please try again.';
                    } finally {
                        this.isPurchasing = false;
                    }
                },

                async checkBalance() {
                    if (this.isCheckingBalance) return;
                    this.isCheckingBalance = true;
                    this.balanceError = '';
                    this.balanceResult = null;

                    try {
                        const response = await fetch(__PINT_BLADE_3__, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': __PINT_BLADE_4__,
                            },
                            body: JSON.stringify({ code: this.balanceCode }),
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.balanceResult = data;
                        } else {
                            this.balanceError = data.error || 'Gift card not found.';
                        }
                    } catch (e) {
                        this.balanceError = 'Something went wrong.';
                    } finally {
                        this.isCheckingBalance = false;
                    }
                },

                copyCode() {
                    if (this.purchasedCard?.code) {
                        navigator.clipboard.writeText(this.purchasedCard.code);
                        this.copied = true;
                        setTimeout(() => (this.copied = false), 2000);
                    }
                },
            };
        }
    </script>
</x-layouts.storefront>
