<x-layouts.storefront>

<div x-data="giftCardPage()">
    {{-- Photo-Forward Hero --}}
    <x-storefront.hero-section :image="$settings->giftCardsHeroImageUrl()" image-alt="Fresh baked goods" image-class="hero-img">
        <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
            <x-storefront.eyebrow line-opacity="0.4" class="hero-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'A Sweet Gesture' }}</x-storefront.eyebrow>
            <h1 class="hero-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
                {!! nl2br(e($content['hero_title'] ?? "Give the Gift of\nFresh Baked Goods")) !!}
            </h1>
            <p class="hero-fade-3 font-script text-2xl md:text-3xl text-warm-400">
                {{ $content['hero_subtitle'] ?? 'A treat they\'ll remember long after the last crumb' }}
            </p>
        </div>
    </x-storefront.hero-section>

    {{-- Success State --}}
    <div x-show="purchasedCard" x-cloak class="py-20 px-4 bg-warm-50">
        <div class="max-w-lg mx-auto text-center">
            {{-- Gift card mockup --}}
            <div class="rounded-2xl p-8 mb-8 shadow-2xl relative overflow-hidden bg-gradient-to-br from-warm-900 to-warm-800">
                <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-10 bg-warm-500 translate-x-[30%] -translate-y-[30%]"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full opacity-10 bg-warm-500 -translate-x-[30%] translate-y-[30%]"></div>
                <div class="relative z-10">
                    <p class="font-script text-xl mb-1 text-warm-500">Gift Card</p>
                    <p class="font-display text-4xl font-bold mb-4 text-white" x-text="'$' + parseFloat(purchasedCard?.balance || 0).toFixed(2)"></p>
                    <div class="py-3 px-6 rounded-xl inline-block mb-2 bg-white/5 border border-warm-400/20">
                        <span class="font-mono text-lg tracking-[0.2em] font-bold text-warm-400" x-text="purchasedCard?.code"></span>
                    </div>
                </div>
            </div>

            <h2 class="font-display text-2xl font-bold mb-2 text-warm-900">{{ $content['success_heading'] ?? 'Gift Card Purchased!' }}</h2>
            <p class="mb-6 text-warm-600">{{ $content['success_description'] ?? 'Share the code below with the lucky recipient.' }}</p>

            <div class="flex justify-center gap-4">
                <x-storefront.button variant="outline-light" size="md" class="gap-2" @click="copyCode()">
                    <x-heroicon-o-document-duplicate class="w-4 h-4" stroke-width="2" />
                    <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                </x-storefront.button>
                <x-storefront.button size="md" @click="purchasedCard = null">
                    Purchase Another
                </x-storefront.button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div x-show="!purchasedCard">
        <section class="py-20 px-4 bg-warm-50">
            <div class="max-w-6xl mx-auto grid lg:grid-cols-5 gap-12">
                {{-- Left: Gift Card Preview + Amount Selection (3 cols) --}}
                <div class="lg:col-span-3 space-y-10">
                    {{-- Card Preview --}}
                    <div>
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-4 text-warm-500">{{ $content['preview_label'] ?? 'Preview' }}</p>
                        <div class="rounded-2xl p-10 shadow-xl relative overflow-hidden aspect-[16/9] flex flex-col justify-between bg-gradient-to-br from-warm-900 to-warm-800">
                            <div class="absolute top-0 right-0 w-60 h-60 rounded-full opacity-[0.06] bg-warm-500 translate-x-[30%] -translate-y-[30%]"></div>
                            <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-[0.06] bg-warm-500 -translate-x-[30%] translate-y-[30%]"></div>
                            <div class="relative z-10">
                                <p class="font-script text-2xl text-warm-500">Gift Card</p>
                                <p class="font-display text-lg mt-1 text-warm-400">{{ $settings->store->name }}</p>
                            </div>
                            <div class="relative z-10 text-right">
                                <p class="font-display text-5xl font-bold text-white" x-text="'$' + parseFloat(form.initial_balance || 0).toFixed(2)"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Selection --}}
                    <div>
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-4 text-warm-500">{{ $content['amount_label'] ?? 'Select Amount' }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                            <template x-for="preset in {{ json_encode($settings->giftCards->presetAmounts) }}" :key="preset">
                                <button type="button"
                                    @click="form.initial_balance = preset; customAmount = ''"
                                    :class="form.initial_balance == preset && !customAmount
                                        ? 'bg-warm-500 text-warm-900 border-warm-500'
                                        : 'bg-white text-warm-800 border-warm-200'"
                                    class="rounded-2xl py-5 text-center font-display text-2xl font-bold border-2 transition-all duration-300 hover:scale-105 hover:shadow-lg cursor-pointer">
                                    <span x-text="'$' + preset"></span>
                                </button>
                            </template>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold whitespace-nowrap text-warm-600">Custom amount:</span>
                            <div class="relative flex-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-warm-500">$</span>
                                <input type="number" x-model="customAmount"
                                       @input="form.initial_balance = customAmount"
                                       min="1" step="0.01" placeholder="0.00"
                                       class="w-full pl-8 pr-4 py-3 rounded-xl border-2 font-semibold focus:outline-none transition-colors border-warm-200 text-warm-800 bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Check Balance --}}
                    <div class="rounded-2xl p-8 bg-white border border-warm-200">
                        <h3 class="font-display text-xl font-semibold mb-4 text-warm-900">{{ $content['balance_heading'] ?? 'Check Gift Card Balance' }}</h3>
                        <form @submit.prevent="checkBalance()" class="flex flex-col sm:flex-row gap-3" data-test="gift-card-balance-form">
                            <input type="text" x-model="balanceCode" required
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="input-field font-mono uppercase tracking-wider flex-1"
                                   data-test="gift-card-balance-form-code">
                            <x-storefront.button type="submit" variant="outline-light" size="md"
                                    x-bind:disabled="!balanceCode || isCheckingBalance"
                                    class="whitespace-nowrap"
                                    x-bind:class="isCheckingBalance ? 'opacity-50 cursor-not-allowed' : ''"
                                    data-test="gift-card-balance-form-submit">
                                <span x-text="isCheckingBalance ? 'Checking...' : {{ Js::from($content['check_balance_button'] ?? 'Check Balance') }}"></span>
                            </x-storefront.button>
                        </form>
                        <div x-show="balanceError" class="text-red-600 text-sm mt-2" x-text="balanceError" data-test="gift-card-balance-error"></div>
                        <div x-show="balanceResult" x-cloak class="mt-6 rounded-xl p-6 text-center bg-warm-50">
                            <p class="text-sm uppercase tracking-wider mb-1 text-warm-500">Current Balance</p>
                            <p class="font-display text-4xl font-bold text-warm-900" x-text="'$' + parseFloat(balanceResult?.current_balance || 0).toFixed(2)"></p>
                            <p class="text-sm mt-2 text-warm-500" x-show="balanceResult?.expires_at">
                                Expires: <span x-text="balanceResult?.expires_at"></span>
                            </p>
                            <p class="text-sm mt-1 text-red-600 font-semibold" x-show="balanceResult && !balanceResult.is_usable">
                                This card is no longer active.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right: Purchase Form (2 cols) --}}
                <div class="lg:col-span-2">
                    <div class="rounded-2xl p-8 sticky top-8 bg-white border border-warm-200 shadow-2xl">
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-1 text-warm-500">{{ $content['details_eyebrow'] ?? 'Details' }}</p>
                        <h2 class="font-display text-2xl font-bold mb-6 text-warm-900">{{ $content['details_heading'] ?? 'Send Your Gift' }}</h2>

                        <form @submit.prevent="purchase()" class="space-y-5" data-test="gift-card-purchase-form">
                            <div>
                                <label class="block text-sm font-semibold mb-1 text-warm-700">Your Name *</label>
                                <input type="text" x-model="form.purchaser_name" required class="input-field" data-test="gift-card-purchase-form-purchaser-name">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1 text-warm-700">Your Email *</label>
                                <input type="email" x-model="form.purchaser_email" required class="input-field" data-test="gift-card-purchase-form-purchaser-email">
                            </div>

                            <div class="pt-4 border-t border-warm-200">
                                <p class="font-script text-lg mb-3 text-warm-500">{{ $content['recipient_label'] ?? 'Recipient Info' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1 text-warm-700">Recipient Name</label>
                                <input type="text" x-model="form.recipient_name" class="input-field" placeholder="Optional" data-test="gift-card-purchase-form-recipient-name">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1 text-warm-700">Recipient Email</label>
                                <input type="email" x-model="form.recipient_email" class="input-field" placeholder="Optional" data-test="gift-card-purchase-form-recipient-email">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1 text-warm-700">Gift Message</label>
                                <textarea x-model="form.message" class="input-field" rows="3" placeholder="Add a personal touch..." data-test="gift-card-purchase-form-message"></textarea>
                            </div>

                            <div x-show="purchaseError" class="text-red-600 text-sm" x-text="purchaseError" data-test="gift-card-purchase-error"></div>

                            <button type="submit"
                                    :disabled="!form.purchaser_name || !form.purchaser_email || !form.initial_balance || isPurchasing"
                                    class="w-full py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
                                    :class="isPurchasing ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="bg-warm-500 text-warm-900"
                                    data-test="gift-card-purchase-form-submit">
                                <span x-text="isPurchasing ? 'Processing...' : 'Purchase — $' + parseFloat(form.initial_balance || 0).toFixed(2)"></span>
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
            initial_balance: {{ $settings->giftCards->defaultAmount }},
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
                const response = await fetch('{{ route("giftCards.purchase") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
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
                const response = await fetch('{{ route("giftCards.balance") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: this.balanceCode })
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
                setTimeout(() => this.copied = false, 2000);
            }
        }
    }
}
</script>
</x-layouts.storefront>
