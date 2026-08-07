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
    <section class="mx-auto max-w-4xl px-4 py-12">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if (! $customer->hasVerifiedEmail())
            <div class="mb-6 flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
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

        <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="font-script text-warm-500 mb-1 text-xl">Welcome back,</p>
                <h1 class="font-display text-warm-900 text-4xl md:text-5xl">{{ $customer->name }}</h1>
            </div>
            <div class="flex items-center gap-5">
                <a
                    href="{{ route('account.profile.show') }}"
                    class="text-warm-700 hover:text-warm-900 text-sm font-semibold underline"
                >
                    Edit profile
                </a>
                <form method="POST" action="{{ route('account.logout') }}">
                    @csrf
                    <button type="submit" class="text-warm-700 hover:text-warm-900 text-sm font-semibold underline">
                        Sign out
                    </button>
                </form>
            </div>
        </div>

        @if ($loyaltyBalance || $referralCode)
            <div class="mb-6 grid gap-6 md:grid-cols-3">
                @if ($loyaltyBalance)
                    <div class="card p-6">
                        <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.2em] uppercase">Reward points</p>
                        <p class="font-display text-warm-900 text-3xl font-bold">@number($loyaltyBalance->total)</p>
                        <a
                            href="{{ route('storefront.rewards') }}"
                            class="text-warm-700 mt-2 inline-block text-xs font-semibold hover:underline"
                        >
                            See rewards &rarr;
                        </a>
                    </div>
                @endif
                @if ($loyaltyTier)
                    <div class="card p-6">
                        <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.2em] uppercase">Your tier</p>
                        <p class="font-display text-warm-900 text-2xl font-bold">{{ $loyaltyTier->getLabel() }}</p>
                    </div>
                @endif
                @if ($referralCode)
                    <div class="card p-6">
                        <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.2em] uppercase">Referral code</p>
                        <p class="font-display text-warm-900 mb-2 text-xl font-bold">{{ $referralCode }}</p>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText({{ json_encode($referralShareUrl) }}); this.textContent='Copied!';"
                            class="text-warm-700 text-xs font-semibold hover:underline"
                        >
                            Copy share link
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <div class="card p-6">
                <h2 class="font-display text-warm-900 mb-4 text-xl">Recent orders</h2>
                @if ($orders->isNotEmpty())
                    <ul class="divide-warm-200 divide-y">
                        @foreach ($orders as $order)
                            <li class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <a
                                        href="{{ route('order.confirmation', $order) }}"
                                        class="text-warm-900 font-semibold hover:underline"
                                    >
                                        Order #{{ $order->order_number }}
                                    </a>
                                    <p class="text-warm-600 text-sm">
                                        {{ $order->created_at?->format('M j, Y') }} &middot; {{ $order->status->getLabel() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-warm-900 font-semibold">@money($order->total)</p>
                                    <div class="mt-1 flex justify-end gap-3">
                                        <a
                                            href="{{ route('order.create') }}?reorder={{ $order->order_number }}"
                                            class="text-warm-700 text-xs font-semibold hover:underline"
                                        >
                                            Reorder
                                        </a>
                                        <a
                                            href="{{ route('order.track') }}?number={{ $order->order_number }}"
                                            class="text-warm-700 text-xs font-semibold hover:underline"
                                        >
                                            Track
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <a
                        href="{{ route('account.orders') }}"
                        class="text-warm-800 mt-4 inline-block text-sm font-semibold hover:underline"
                    >
                        View all orders &rarr;
                    </a>
                @else
                    <p class="text-warm-600">You haven't placed any orders yet.</p>
                    <a
                        href="{{ route('order.create') }}"
                        class="text-warm-800 mt-3 inline-block text-sm font-semibold hover:underline"
                    >
                        Start an order &rarr;
                    </a>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="font-display text-warm-900 mb-4 text-xl">Favorites</h2>
                @if ($favorites->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach ($favorites as $favorite)
                            @if ($favorite->product)
                                <li class="flex items-center justify-between">
                                    <span class="text-warm-800">{{ $favorite->product->name }}</span>
                                    <span class="text-warm-900 text-sm font-semibold">
                                        @money($favorite->product->price)
                                    </span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <p class="text-warm-600">Tap the heart on a product to save it here.</p>
                    <a
                        href="{{ route('storefront.menu') }}"
                        class="text-warm-800 mt-3 inline-block text-sm font-semibold hover:underline"
                    >
                        Browse the menu &rarr;
                    </a>
                @endif
            </div>

            <div class="card p-6 md:col-span-2">
                <h2 class="font-display text-warm-900 mb-4 text-xl">Your details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-warm-500 mb-1 text-xs tracking-wider uppercase">Email</dt>
                        <dd class="text-warm-900">{{ $customer->email }}</dd>
                    </div>
                    @if ($customer->phone)
                        <div>
                            <dt class="text-warm-500 mb-1 text-xs tracking-wider uppercase">Phone</dt>
                            <dd class="text-warm-900">{{ $customer->phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>
</x-layouts.storefront>
