@extends('layouts.storefront')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12" x-data="giftCardPage()">

    <div class="text-center mb-12">
        <h1 class="font-display text-4xl font-bold text-warm-900 mb-4">🎁 Gift Cards</h1>
        <p class="text-warm-700 text-lg">Give the gift of fresh-baked goodness!</p>
    </div>

    {{-- Success: Show purchased card --}}
    <div x-show="purchasedCard" x-cloak class="card p-8 text-center mb-12">
        <div class="text-5xl mb-4">🎉</div>
        <h2 class="font-display text-2xl font-bold text-warm-900 mb-2">Gift Card Purchased!</h2>
        <p class="text-warm-700 mb-6">Here's the gift card code:</p>
        <div class="bg-warm-100 rounded-xl py-6 px-8 inline-block mb-6">
            <span class="font-mono text-3xl font-bold tracking-widest text-warm-900" x-text="purchasedCard?.code"></span>
        </div>
        <p class="text-warm-600 text-sm mb-4">
            Balance: <strong x-text="'$' + parseFloat(purchasedCard?.balance || 0).toFixed(2)"></strong>
        </p>
        <div class="flex justify-center gap-4">
            <button @click="copyCode()" class="btn-secondary">
                <span x-text="copied ? '✓ Copied!' : '📋 Copy Code'"></span>
            </button>
            <button @click="purchasedCard = null" class="btn-primary">Purchase Another</button>
        </div>
    </div>

    <div x-show="!purchasedCard" class="grid md:grid-cols-2 gap-8">
        {{-- Purchase Form --}}
        <div class="card p-6">
            <h2 class="font-display text-xl font-semibold text-warm-900 mb-6">Purchase a Gift Card</h2>

            <form @submit.prevent="purchase()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-1">Your Name *</label>
                        <input type="text" x-model="form.purchaser_name" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-1">Your Email *</label>
                        <input type="email" x-model="form.purchaser_email" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-1">Recipient Name</label>
                        <input type="text" x-model="form.recipient_name" class="input-field" placeholder="Optional">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-1">Recipient Email</label>
                        <input type="email" x-model="form.recipient_email" class="input-field" placeholder="Optional">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-1">Gift Message</label>
                        <textarea x-model="form.message" class="input-field" rows="3" placeholder="Add a personal message..."></textarea>
                    </div>

                    {{-- Amount Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-warm-900 mb-2">Amount *</label>
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            <template x-for="preset in [10, 25, 50, 100]" :key="preset">
                                <button type="button"
                                        @click="form.initial_balance = preset; customAmount = ''"
                                        :class="form.initial_balance == preset && !customAmount ? 'btn-primary' : 'btn-secondary'"
                                        class="py-2 text-center">
                                    <span x-text="'$' + preset"></span>
                                </button>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-warm-700 text-sm">Custom:</span>
                            <input type="number" x-model="customAmount"
                                   @input="form.initial_balance = customAmount"
                                   min="1" step="0.01" placeholder="$0.00"
                                   class="input-field flex-1">
                        </div>
                    </div>

                    <div x-show="purchaseError" class="text-red-600 text-sm" x-text="purchaseError"></div>

                    <button type="submit"
                            :disabled="!form.purchaser_name || !form.purchaser_email || !form.initial_balance || isPurchasing"
                            class="w-full btn-primary py-3"
                            :class="isPurchasing ? 'opacity-50 cursor-not-allowed' : ''">
                        <span x-text="isPurchasing ? 'Processing...' : 'Purchase Gift Card — $' + parseFloat(form.initial_balance || 0).toFixed(2)"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Check Balance --}}
        <div>
            <div class="card p-6">
                <h2 class="font-display text-xl font-semibold text-warm-900 mb-6">Check Gift Card Balance</h2>

                <form @submit.prevent="checkBalance()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-warm-900 mb-1">Gift Card Code</label>
                            <input type="text" x-model="balanceCode" required
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="input-field font-mono uppercase tracking-wider">
                        </div>

                        <div x-show="balanceError" class="text-red-600 text-sm" x-text="balanceError"></div>

                        <button type="submit"
                                :disabled="!balanceCode || isCheckingBalance"
                                class="w-full btn-secondary py-3"
                                :class="isCheckingBalance ? 'opacity-50 cursor-not-allowed' : ''">
                            <span x-text="isCheckingBalance ? 'Checking...' : 'Check Balance'"></span>
                        </button>
                    </div>
                </form>

                <div x-show="balanceResult" x-cloak class="mt-6 bg-warm-50 rounded-lg p-4 text-center">
                    <p class="text-warm-700 text-sm mb-1">Current Balance</p>
                    <p class="font-display text-3xl font-bold text-warm-900" x-text="'$' + parseFloat(balanceResult?.current_balance || 0).toFixed(2)"></p>
                    <p class="text-warm-600 text-xs mt-2" x-show="balanceResult?.expires_at">
                        Expires: <span x-text="balanceResult?.expires_at"></span>
                    </p>
                    <p class="text-warm-600 text-xs mt-1" x-show="balanceResult && !balanceResult.is_usable">
                        <span class="text-red-600 font-semibold">This card is no longer active.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function giftCardPage() {
    return {
        form: {
            purchaser_name: '',
            purchaser_email: localStorage.getItem('customer_email') || '',
            recipient_name: '',
            recipient_email: '',
            message: '',
            initial_balance: 25,
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
                const response = await fetch('{{ route("gift-cards.purchase") }}', {
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
                const response = await fetch('{{ route("gift-cards.balance") }}', {
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
@endsection
