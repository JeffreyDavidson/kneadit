@php
    /** @var \App\ViewModels\Storefront\LoyaltyPageViewModel $vm */
@endphp

<x-layouts.storefront>
    {{-- Photo-Forward Hero --}}
    <x-storefront.hero-section
        :image="$vm->settings->loyaltyHeroImageUrl()"
        image-alt="Fresh baked goods"
        image-class="hero-img"
    >
        <div class="relative z-10 mx-auto flex min-h-[55vh] max-w-4xl flex-col justify-end px-4 pb-20 text-center">
            <x-storefront.eyebrow line-opacity="0.4" class="hero-fade-1 mb-6">
                {{ $vm->content['hero_eyebrow'] ?? 'Rewards Program' }}</x-storefront.eyebrow>
            <h1 class="hero-fade-2 font-display mb-6 text-4xl leading-tight font-bold text-white md:text-6xl">
                {{ $vm->settings->store->name }} {{ $vm->settings->loyalty->programName }}
            </h1>
            <p class="hero-fade-3 font-script text-warm-400 text-2xl md:text-3xl">
                {{ $vm->content['hero_subtitle'] ?? 'Earn points with every order, unlock delicious rewards' }}
            </p>
        </div>
    </x-storefront.hero-section>

    @if (! $vm->settings->loyalty->enabled)
        <section class="bg-warm-50 px-4 py-20">
            <div class="border-warm-200 mx-auto max-w-lg rounded-2xl border bg-white p-12 text-center">
                <p class="font-display text-warm-700 text-xl">
                    {{ $vm->content['paused_message'] ?? 'Our loyalty program is currently paused. Check back soon!' }}
                </p>
            </div>
        </section>
    @else
        {{-- Check Points / Customer Dashboard --}}
        <section class="bg-warm-50 px-4 py-20">
            <div class="mx-auto max-w-5xl">
                {{-- Points Lookup --}}
                <div class="mx-auto mb-16 max-w-xl">
                    <div class="border-warm-200 rounded-2xl border bg-white p-8 shadow-2xl">
                        <h2 class="font-display text-warm-900 mb-4 text-center text-2xl font-bold">
                            {{ $vm->content['check_heading'] ?? 'Check Your Points' }}
                        </h2>
                        <form
                            action="{{ route('rewards.check') }}"
                            method="POST"
                            class="flex flex-col gap-3 sm:flex-row"
                            data-test="loyalty-lookup-form"
                        >
                            @csrf
                            <input
                                type="email"
                                name="email"
                                placeholder="Enter your email address"
                                value="{{ old('email', $vm->customer->email ?? '') }}"
                                required
                                class="input-field flex-1"
                                data-test="loyalty-lookup-form-email"
                            />
                            <x-storefront.button
                                type="submit"
                                size="md"
                                class="whitespace-nowrap"
                                data-test="loyalty-lookup-form-submit"
                            >
                                Check Balance
                            </x-storefront.button>
                        </form>
                    </div>
                </div>

                {{-- Customer Results --}}
                @if ($vm->hasCustomer)
                    <div class="mb-16">
                        <p class="font-script text-warm-500 mb-8 text-center text-2xl">
                            Welcome back, {{ $vm->customer->name }}!
                        </p>

                        {{-- Points Display --}}
                        <div class="mx-auto mb-10 grid max-w-2xl gap-6 sm:grid-cols-2">
                            <div class="bg-warm-900 relative overflow-hidden rounded-2xl p-8 text-center">
                                <div class="bg-warm-500 absolute top-0 right-0 h-32 w-32 translate-x-[30%] -translate-y-[30%] rounded-full opacity-[0.06]"></div>
                                <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.25em] uppercase">
                                    Available Points
                                </p>
                                <p class="font-display text-warm-500 text-5xl font-bold">@number($vm->totalPoints)</p>
                            </div>
                            <div class="border-warm-200 rounded-2xl border bg-white p-8 text-center">
                                <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.25em] uppercase">
                                    Lifetime Earned
                                </p>
                                <p class="font-display text-warm-900 text-5xl font-bold">
                                    @number($vm->lifetimeEarned)
                                </p>
                            </div>
                        </div>

                        @if ($vm->tier)
                            <div class="border-warm-200 mx-auto mb-10 max-w-2xl rounded-2xl border bg-white p-6">
                                <div class="mb-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-warm-500 mb-1 text-xs font-semibold tracking-[0.2em] uppercase">
                                            Your Tier
                                        </p>
                                        <p class="font-display text-warm-900 text-2xl font-bold">
                                            {{ $vm->tier->getLabel() }}
                                        </p>
                                    </div>
                                    @if ($vm->nextTier)
                                        <div class="text-right">
                                            <p class="text-warm-500 text-xs">Next: {{ $vm->nextTier->getLabel() }}</p>
                                            <p class="text-warm-700 text-sm font-bold">
                                                @number($vm->pointsToNextTier)
                                                pts to go
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-warm-500 text-xs">Top tier — thanks for the love!</p>
                                    @endif
                                </div>
                                @if ($vm->tierMultiplier > 1.0 || $vm->tierFreeDelivery)
                                    <div class="border-warm-100 flex flex-wrap gap-3 border-t pt-3">
                                        @if ($vm->tierMultiplier > 1.0)
                                            <span class="bg-warm-50 text-warm-700 border-warm-200 inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold">
                                                {{ rtrim(rtrim(number_format($vm->tierMultiplier, 1), '0'), '.') }}×
                                                points
                                            </span>
                                        @endif
                                        @if ($vm->tierFreeDelivery)
                                            <span class="bg-warm-50 text-warm-700 border-warm-200 inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold">
                                                Free delivery
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Progress to next reward --}}
                        @if ($vm->nextReward)
                            <div class="border-warm-200 mx-auto mb-10 max-w-2xl rounded-2xl border bg-white p-6">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-warm-700 text-sm font-semibold">Next Reward: {{ $vm->nextReward->name }}</span>
                                    <span class="text-warm-500 text-sm font-bold">
                                        @number($vm->totalPoints)
                                        /
                                        @number($vm->nextReward->points_required)
                                        pts</span>
                                </div>
                                <div class="bg-warm-200 h-3 w-full overflow-hidden rounded-full">
                                    <div
                                        class="from-warm-500 to-warm-400 h-full rounded-full bg-gradient-to-r transition-all duration-700"
                                        style="width: {{ $vm->nextRewardProgressPercent() }}%;"
                                    ></div>
                                </div>
                                <p class="text-warm-500 mt-2 text-right text-xs">
                                    @number($vm->pointsToNextReward())
                                    points to go!
                                </p>
                            </div>
                        @endif

                        {{-- Transaction History --}}
                        @if ($vm->history->count())
                            <div class="border-warm-200 mx-auto max-w-2xl overflow-hidden rounded-2xl border bg-white">
                                <div class="border-warm-200 border-b px-6 py-4">
                                    <h3 class="font-display text-warm-900 text-lg font-bold">Points History</h3>
                                </div>
                                <div class="divide-warm-100 divide-y">
                                    @foreach ($vm->history as $entry)
                                        <div class="hover:bg-warm-50 flex items-center justify-between px-6 py-4 transition-colors">
                                            <div>
                                                <p class="text-warm-900 font-semibold">{{ $entry->description }}</p>
                                                <p class="text-warm-500 text-sm">
                                                    {{ $entry->created_at->format('M j, Y') }}
                                                </p>
                                            </div>
                                            <span class="font-display text-lg font-bold {{ $vm->historyEntryColorClass($entry->type) }}">
                                                {{ $vm->historyEntrySign($entry->type) }}
                                                @number($entry->points)
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($vm->customerNotFound)
                    <div class="border-warm-200 mx-auto mb-16 max-w-xl rounded-2xl border bg-white p-8 text-center">
                        <p class="text-warm-700">
                            We couldn't find an account with that email. Points are earned automatically when your
                            orders are delivered!
                        </p>
                    </div>
                @endif

                {{-- Available Rewards --}}
                @if ($vm->rewards->count())
                    <div class="mb-16">
                        <div class="mb-10 text-center">
                            <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.25em] uppercase">
                                {{ $vm->content['rewards_eyebrow'] ?? 'Unlock' }}
                            </p>
                            <h2 class="font-display text-warm-900 text-3xl font-bold">
                                {{ $vm->content['rewards_heading'] ?? 'Available Rewards' }}
                            </h2>
                        </div>
                        <div class="mx-auto grid max-w-4xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($vm->rewards as $reward)
                                <div @class([
                                    'rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden bg-white border-2',
                                    'border-warm-500' => $vm->canRedeem($reward),
                                    'border-warm-200' => ! $vm->canRedeem($reward),
                                ])>
                                    @if ($vm->canRedeem($reward))
                                        <x-storefront.pill
                                            tone="solid"
                                            size="xs"
                                            class="absolute top-3 right-3 !font-bold"
                                        >
                                            Redeemable!</x-storefront.pill>
                                    @endif
                                    <div class="bg-warm-100 mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full">
                                        <x-heroicon-o-chat-bubble-oval-left class="text-warm-500 h-7 w-7" />
                                    </div>
                                    <h3 class="font-display text-warm-900 mb-1 text-lg font-bold">
                                        {{ $reward->name }}
                                    </h3>
                                    @if ($reward->description)
                                        <p class="text-warm-600 mb-3 text-sm">{{ $reward->description }}</p>
                                    @endif
                                    <div class="bg-warm-100 inline-block rounded-full px-4 py-2">
                                        <span class="font-display text-warm-800 font-bold">
                                            @number($reward->points_required)
                                        </span>
                                        <span class="text-warm-500 text-sm">pts</span>
                                    </div>
                                    <p class="text-warm-500 mt-2 text-xs">
                                        {{ \App\Presenters\LoyaltyRewardPresenter::for($reward)->rewardTypeLabel() }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- How It Works --}}
        <x-storefront.dark-section :show-radial="false" padding="py-20">
            <div class="mx-auto max-w-4xl px-4">
                <div class="mb-12 text-center">
                    <p class="text-warm-500 mb-2 text-xs font-semibold tracking-[0.25em] uppercase">
                        {{ $vm->content['how_it_works_eyebrow'] ?? 'Simple as' }}
                    </p>
                    <h2 class="font-display text-3xl font-bold text-white md:text-4xl">
                        {{ $vm->content['how_it_works_heading'] ?? 'How It Works' }}
                    </h2>
                </div>
                <div class="grid gap-10 text-center sm:grid-cols-3">
                    @foreach ($vm->howSteps as $step)
                        <div>
                            <x-storefront.icon-circle size="lg" variant="subtle" class="mx-auto mb-5">
                                <x-dynamic-component
                                    :component="'heroicon-o-' . ($step['icon'] ?? 'star')"
                                    class="text-warm-500 h-8 w-8"
                                />
                            </x-storefront.icon-circle>
                            <h3 class="font-display text-warm-200 mb-2 text-xl font-bold">{{ $step['title'] }}</h3>
                            <p class="text-warm-500">{{ $step['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-storefront.dark-section>
    @endif
</x-layouts.storefront>
