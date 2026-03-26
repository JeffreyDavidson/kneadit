@extends('layouts.storefront')

@section('content')
@php
    $heroImage = settings('gift_cards_hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

    $content = settingsPageContent('gift_cards');
@endphp

<style>
    @keyframes giftKenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.06); } }
    @keyframes giftFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .gift-hero-img { animation: giftKenBurns 25s ease-in-out infinite alternate; }
    .gift-fade-1 { animation: giftFadeUp 0.8s ease-out 0.3s both; }
    .gift-fade-2 { animation: giftFadeUp 0.8s ease-out 0.5s both; }
    .gift-fade-3 { animation: giftFadeUp 0.8s ease-out 0.7s both; }
</style>

<div x-data="giftCardPage()">
    {{-- Photo-Forward Hero --}}
    <section class="relative overflow-hidden" style="min-height: 55vh;">
        <div class="absolute inset-0">
            <img src="{{ $heroImageUrl }}" alt="Fresh baked goods" class="w-full h-full object-cover gift-hero-img">
        </div>
        <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

        <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
            <div class="gift-fade-1 flex items-center justify-center gap-4 mb-6">
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'A Sweet Gesture' }}</span>
                <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            </div>
            <h1 class="gift-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight" style="color: white;">
                {!! nl2br(e($content['hero_title'] ?? "Give the Gift of\nFresh Baked Goods")) !!}
            </h1>
            <p class="gift-fade-3 font-script text-2xl md:text-3xl" style="color: var(--warm-400);">
                {{ $content['hero_subtitle'] ?? 'A treat they\'ll remember long after the last crumb' }}
            </p>
        </div>
    </section>

    {{-- Success State --}}
    <div x-show="purchasedCard" x-cloak class="py-20 px-4" style="background: var(--warm-50);">
        <div class="max-w-lg mx-auto text-center">
            {{-- Gift card mockup --}}
            <div class="rounded-2xl p-8 mb-8 shadow-2xl relative overflow-hidden" style="background: linear-gradient(135deg, var(--warm-900) 0%, var(--warm-800) 100%);">
                <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-10" style="background: var(--warm-500); transform: translate(30%, -30%);"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full opacity-10" style="background: var(--warm-500); transform: translate(-30%, 30%);"></div>
                <div class="relative z-10">
                    <p class="font-script text-xl mb-1" style="color: var(--warm-500);">Gift Card</p>
                    <p class="font-display text-4xl font-bold mb-4" style="color: white;" x-text="'$' + parseFloat(purchasedCard?.balance || 0).toFixed(2)"></p>
                    <div class="py-3 px-6 rounded-xl inline-block mb-2" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(232,176,74,0.2);">
                        <span class="font-mono text-lg tracking-[0.2em] font-bold" style="color: var(--warm-400);" x-text="purchasedCard?.code"></span>
                    </div>
                </div>
            </div>

            <h2 class="font-display text-2xl font-bold mb-2" style="color: var(--warm-900);">{{ $content['success_heading'] ?? 'Gift Card Purchased!' }}</h2>
            <p class="mb-6" style="color: var(--warm-600);">{{ $content['success_description'] ?? 'Share the code below with the lucky recipient.' }}</p>

            <div class="flex justify-center gap-4">
                <button @click="copyCode()" class="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="border: 2px solid var(--warm-300); color: var(--warm-700);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                </button>
                <button @click="purchasedCard = null" class="px-6 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                    Purchase Another
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div x-show="!purchasedCard">
        <section class="py-20 px-4" style="background: var(--warm-50);">
            <div class="max-w-6xl mx-auto grid lg:grid-cols-5 gap-12">
                {{-- Left: Gift Card Preview + Amount Selection (3 cols) --}}
                <div class="lg:col-span-3 space-y-10">
                    {{-- Card Preview --}}
                    <div>
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-4" style="color: var(--warm-500);">{{ $content['preview_label'] ?? 'Preview' }}</p>
                        <div class="rounded-2xl p-10 shadow-xl relative overflow-hidden aspect-[16/9] flex flex-col justify-between" style="background: linear-gradient(135deg, var(--warm-900) 0%, var(--warm-800) 100%);">
                            <div class="absolute top-0 right-0 w-60 h-60 rounded-full opacity-[0.06]" style="background: var(--warm-500); transform: translate(30%, -30%);"></div>
                            <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-[0.06]" style="background: var(--warm-500); transform: translate(-30%, 30%);"></div>
                            <div class="relative z-10">
                                <p class="font-script text-2xl" style="color: var(--warm-500);">Gift Card</p>
                                <p class="font-display text-lg mt-1" style="color: var(--warm-400);">{{ settings('store_name', 'Our Bakery') }}</p>
                            </div>
                            <div class="relative z-10 text-right">
                                <p class="font-display text-5xl font-bold" style="color: white;" x-text="'$' + parseFloat(form.initial_balance || 0).toFixed(2)"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Selection --}}
                    <div>
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-4" style="color: var(--warm-500);">{{ $content['amount_label'] ?? 'Select Amount' }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                            <template x-for="preset in [10, 25, 50, 100]" :key="preset">
                                <button type="button"
                                    @click="form.initial_balance = preset; customAmount = ''"
                                    :style="form.initial_balance == preset && !customAmount
                                        ? 'background: var(--warm-500); color: var(--warm-900); border-color: var(--warm-500);'
                                        : 'background: white; color: var(--warm-800); border-color: var(--warm-200);'"
                                    class="rounded-2xl py-5 text-center font-display text-2xl font-bold border-2 transition-all duration-300 hover:scale-105 hover:shadow-lg cursor-pointer">
                                    <span x-text="'$' + preset"></span>
                                </button>
                            </template>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold whitespace-nowrap" style="color: var(--warm-600);">Custom amount:</span>
                            <div class="relative flex-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-semibold" style="color: var(--warm-500);">$</span>
                                <input type="number" x-model="customAmount"
                                       @input="form.initial_balance = customAmount"
                                       min="1" step="0.01" placeholder="0.00"
                                       class="w-full pl-8 pr-4 py-3 rounded-xl border-2 font-semibold focus:outline-none transition-colors" style="border-color: var(--warm-200); color: var(--warm-800); background: white;">
                            </div>
                        </div>
                    </div>

                    {{-- Check Balance --}}
                    <div class="rounded-2xl p-8" style="background: white; border: 1px solid var(--warm-200);">
                        <h3 class="font-display text-xl font-semibold mb-4" style="color: var(--warm-900);">{{ $content['balance_heading'] ?? 'Check Gift Card Balance' }}</h3>
                        <form @submit.prevent="checkBalance()" class="flex flex-col sm:flex-row gap-3">
                            <input type="text" x-model="balanceCode" required
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="input-field font-mono uppercase tracking-wider flex-1">
                            <button type="submit"
                                    :disabled="!balanceCode || isCheckingBalance"
                                    class="px-6 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 whitespace-nowrap"
                                    :class="isCheckingBalance ? 'opacity-50 cursor-not-allowed' : ''"
                                    style="border: 2px solid var(--warm-300); color: var(--warm-700);">
                                <span x-text="isCheckingBalance ? 'Checking...' : 'Check Balance'"></span>
                            </button>
                        </form>
                        <div x-show="balanceError" class="text-red-600 text-sm mt-2" x-text="balanceError"></div>
                        <div x-show="balanceResult" x-cloak class="mt-6 rounded-xl p-6 text-center" style="background: var(--warm-50);">
                            <p class="text-sm uppercase tracking-wider mb-1" style="color: var(--warm-500);">Current Balance</p>
                            <p class="font-display text-4xl font-bold" style="color: var(--warm-900);" x-text="'$' + parseFloat(balanceResult?.current_balance || 0).toFixed(2)"></p>
                            <p class="text-sm mt-2" style="color: var(--warm-500);" x-show="balanceResult?.expires_at">
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
                    <div class="rounded-2xl p-8 sticky top-8" style="background: white; border: 1px solid var(--warm-200); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
                        <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-1" style="color: var(--warm-500);">{{ $content['details_eyebrow'] ?? 'Details' }}</p>
                        <h2 class="font-display text-2xl font-bold mb-6" style="color: var(--warm-900);">{{ $content['details_heading'] ?? 'Send Your Gift' }}</h2>

                        <form @submit.prevent="purchase()" class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Your Name *</label>
                                <input type="text" x-model="form.purchaser_name" required class="input-field">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Your Email *</label>
                                <input type="email" x-model="form.purchaser_email" required class="input-field">
                            </div>

                            <div class="pt-4" style="border-top: 1px solid var(--warm-200);">
                                <p class="font-script text-lg mb-3" style="color: var(--warm-500);">{{ $content['recipient_label'] ?? 'Recipient Info' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Recipient Name</label>
                                <input type="text" x-model="form.recipient_name" class="input-field" placeholder="Optional">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Recipient Email</label>
                                <input type="email" x-model="form.recipient_email" class="input-field" placeholder="Optional">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Gift Message</label>
                                <textarea x-model="form.message" class="input-field" rows="3" placeholder="Add a personal touch..."></textarea>
                            </div>

                            <div x-show="purchaseError" class="text-red-600 text-sm" x-text="purchaseError"></div>

                            <button type="submit"
                                    :disabled="!form.purchaser_name || !form.purchaser_email || !form.initial_balance || isPurchasing"
                                    class="w-full py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
                                    :class="isPurchasing ? 'opacity-50 cursor-not-allowed' : ''"
                                    style="background: var(--warm-500); color: var(--warm-900);">
                                <span x-text="isPurchasing ? 'Processing...' : 'Purchase — $' + parseFloat(form.initial_balance || 0).toFixed(2)"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
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
