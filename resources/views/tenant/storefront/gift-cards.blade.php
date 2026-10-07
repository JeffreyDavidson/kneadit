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

        {{-- Main Content --}}
        <div>
            <section class="bg-warm-50 px-4 py-20">
                <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-5">
                    {{-- Left: Gift Card Preview + Balance Check (3 cols) --}}
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
                                <label for="gift-card-balance-code" class="sr-only">Gift card code</label>
                                <input
                                    id="gift-card-balance-code"
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

                    {{-- Right: In-store purchase notice (2 cols) --}}
                    <div class="lg:col-span-2">
                        <div
                            class="border-warm-200 sticky top-8 rounded-2xl border bg-white p-8 shadow-2xl"
                            data-test="gift-card-in-store-notice"
                        >
                            <p class="text-warm-500 mb-1 text-xs font-semibold tracking-[0.25em] uppercase">
                                {{ $content['details_eyebrow'] ?? 'Details' }}
                            </p>
                            <h2 class="font-display text-warm-900 mb-4 text-2xl font-bold">
                                {{ $content['in_store_heading'] ?? 'Buy a Gift Card at the Bakery' }}
                            </h2>
                            <p class="text-warm-600">
                                {{ $content['in_store_description'] ?? 'Gift cards are available in person at the bakery. Stop by and we\'ll load one with any amount you like.' }}
                            </p>
                            @if ($settings->store->address)
                                <p class="text-warm-700 mt-4 font-semibold">{{ $settings->store->address }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script @cspnonce>
        function giftCardPage() {
            return {
                balanceCode: '',
                isCheckingBalance: false,
                balanceError: '',
                balanceResult: null,

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
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({ code: this.balanceCode }),
                        });

                        const payload = await response.json();
                        if (response.ok && payload.data) {
                            this.balanceResult = payload.data;
                        } else {
                            this.balanceError = payload.message || 'Gift card not found.';
                        }
                    } catch (e) {
                        this.balanceError = 'Something went wrong.';
                    } finally {
                        this.isCheckingBalance = false;
                    }
                },
            };
        }
    </script>
</x-layouts.storefront>
