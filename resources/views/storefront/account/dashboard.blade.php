@php
    /** @var \App\Models\Customers\Customer $customer */
    /** @var \Illuminate\Support\Collection $orders */
    /** @var \Illuminate\Support\Collection $favorites */
    /** @var \App\ValueObjects\LoyaltyBalance|null $loyaltyBalance */
    /** @var \App\Enums\Engagement\LoyaltyTier|null $loyaltyTier */
    /** @var string|null $referralCode */
    /** @var string|null $referralShareUrl */
@endphp
<x-layouts.storefront>
    <section class="max-w-4xl mx-auto px-4 py-12">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if (! $customer->hasVerifiedEmail())
            <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-amber-900">
                    <strong>Verify your email</strong> to see past orders tied to {{ $customer->email }}.
                </p>
                <form method="POST" action="{{ route('account.email.verify.send') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-amber-900 hover:underline">
                        Resend verification email &rarr;
                    </button>
                </form>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-10 gap-4">
            <div>
                <p class="font-script text-xl text-warm-500 mb-1">Welcome back,</p>
                <h1 class="font-display text-4xl md:text-5xl text-warm-900">{{ $customer->name }}</h1>
            </div>
            <form method="POST" action="{{ route('account.logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-warm-700 hover:text-warm-900 underline">
                    Sign out
                </button>
            </form>
        </div>

        @if ($loyaltyBalance || $referralCode)
            <div class="grid gap-6 md:grid-cols-3 mb-6">
                @if ($loyaltyBalance)
                    <div class="card p-6">
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-warm-500 mb-2">Reward points</p>
                        <p class="font-display text-3xl font-bold text-warm-900">@number($loyaltyBalance->total)</p>
                        <a href="{{ route('storefront.rewards') }}" class="text-xs font-semibold text-warm-700 hover:underline mt-2 inline-block">
                            See rewards &rarr;
                        </a>
                    </div>
                @endif
                @if ($loyaltyTier)
                    <div class="card p-6">
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-warm-500 mb-2">Your tier</p>
                        <p class="font-display text-2xl font-bold text-warm-900">{{ $loyaltyTier->getLabel() }}</p>
                    </div>
                @endif
                @if ($referralCode)
                    <div class="card p-6">
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-warm-500 mb-2">Referral code</p>
                        <p class="font-display text-xl font-bold text-warm-900 mb-2">{{ $referralCode }}</p>
                        <button type="button"
                            onclick="navigator.clipboard.writeText({{ json_encode($referralShareUrl) }}); this.textContent='Copied!';"
                            class="text-xs font-semibold text-warm-700 hover:underline">
                            Copy share link
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <div class="card p-6">
                <h2 class="font-display text-xl text-warm-900 mb-4">Recent orders</h2>
                @if ($orders->isNotEmpty())
                    <ul class="divide-y divide-warm-200">
                        @foreach ($orders as $order)
                            <li class="py-3 flex items-center justify-between gap-4">
                                <div>
                                    <a href="{{ route('order.confirmation', $order) }}" class="font-semibold text-warm-900 hover:underline">
                                        Order #{{ $order->order_number }}
                                    </a>
                                    <p class="text-sm text-warm-600">
                                        {{ $order->created_at?->format('M j, Y') }}
                                        &middot; {{ $order->status->getLabel() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-warm-900">@money($order->total)</p>
                                    <div class="flex gap-3 justify-end mt-1">
                                        <a href="{{ route('order.create') }}?reorder={{ $order->order_number }}"
                                           class="text-xs font-semibold text-warm-700 hover:underline">
                                            Reorder
                                        </a>
                                        <a href="{{ route('order.track') }}?number={{ $order->order_number }}"
                                           class="text-xs font-semibold text-warm-700 hover:underline">
                                            Track
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('account.orders') }}" class="inline-block mt-4 text-sm font-semibold text-warm-800 hover:underline">
                        View all orders &rarr;
                    </a>
                @else
                    <p class="text-warm-600">You haven't placed any orders yet.</p>
                    <a href="{{ route('order.create') }}" class="inline-block mt-3 text-sm font-semibold text-warm-800 hover:underline">
                        Start an order &rarr;
                    </a>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="font-display text-xl text-warm-900 mb-4">Favorites</h2>
                @if ($favorites->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach ($favorites as $favorite)
                            @if ($favorite->product)
                                <li class="flex items-center justify-between">
                                    <span class="text-warm-800">{{ $favorite->product->name }}</span>
                                    <span class="text-sm font-semibold text-warm-900">@money($favorite->product->price)</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <p class="text-warm-600">Tap the heart on a product to save it here.</p>
                    <a href="{{ route('storefront.menu') }}" class="inline-block mt-3 text-sm font-semibold text-warm-800 hover:underline">
                        Browse the menu &rarr;
                    </a>
                @endif
            </div>

            <div class="card p-6 md:col-span-2">
                <h2 class="font-display text-xl text-warm-900 mb-4">Your details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-warm-500 uppercase tracking-wider text-xs mb-1">Email</dt>
                        <dd class="text-warm-900">{{ $customer->email }}</dd>
                    </div>
                    @if ($customer->phone)
                        <div>
                            <dt class="text-warm-500 uppercase tracking-wider text-xs mb-1">Phone</dt>
                            <dd class="text-warm-900">{{ $customer->phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>
</x-layouts.storefront>
