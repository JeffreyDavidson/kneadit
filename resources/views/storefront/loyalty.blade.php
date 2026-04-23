@php
/** @var \App\ViewModels\Storefront\LoyaltyPageViewModel $vm */
@endphp

<x-layouts.storefront>

{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$vm->settings->loyaltyHeroImageUrl()" image-alt="Fresh baked goods" image-class="hero-img">
    <div class="relative z-10 flex flex-col justify-end min-h-[55vh] max-w-4xl mx-auto text-center px-4 pb-20">
        <x-storefront.eyebrow line-opacity="0.4" class="hero-fade-1 mb-6">{{ $vm->content['hero_eyebrow'] ?? 'Rewards Program' }}</x-storefront.eyebrow>
        <h1 class="hero-fade-2 font-display text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
            {{ $vm->settings->store->name }} {{ $vm->settings->loyalty->programName }}
        </h1>
        <p class="hero-fade-3 font-script text-2xl md:text-3xl text-warm-400">
            {{ $vm->content['hero_subtitle'] ?? 'Earn points with every order, unlock delicious rewards' }}
        </p>
    </div>
</x-storefront.hero-section>

@if (!$vm->settings->loyalty->enabled)
<section class="py-20 px-4 bg-warm-50">
    <div class="max-w-lg mx-auto text-center rounded-2xl p-12 bg-white border border-warm-200">
        <p class="font-display text-xl text-warm-700">{{ $vm->content['paused_message'] ?? 'Our loyalty program is currently paused. Check back soon!' }}</p>
    </div>
</section>
@else

{{-- Check Points / Customer Dashboard --}}
<section class="py-20 px-4 bg-warm-50">
    <div class="max-w-5xl mx-auto">

        {{-- Points Lookup --}}
        <div class="max-w-xl mx-auto mb-16">
            <div class="rounded-2xl p-8 bg-white border border-warm-200 shadow-2xl">
                <h2 class="font-display text-2xl font-bold mb-4 text-center text-warm-900">{{ $vm->content['check_heading'] ?? 'Check Your Points' }}</h2>
                <form action="{{ route('rewards.check') }}" method="POST" class="flex flex-col sm:flex-row gap-3" data-test="loyalty-lookup-form">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email address"
                           value="{{ old('email', $vm->customer->email ?? '') }}" required class="input-field flex-1"
                           data-test="loyalty-lookup-form-email">
                    <x-storefront.button type="submit" size="md" class="whitespace-nowrap" data-test="loyalty-lookup-form-submit">
                        Check Balance
                    </x-storefront.button>
                </form>
            </div>
        </div>

        {{-- Customer Results --}}
        @if ($vm->hasCustomer)
        <div class="mb-16">
            <p class="font-script text-2xl text-center mb-8 text-warm-500">Welcome back, {{ $vm->customer->name }}!</p>

            {{-- Points Display --}}
            <div class="grid sm:grid-cols-2 gap-6 max-w-2xl mx-auto mb-10">
                <div class="rounded-2xl p-8 text-center relative overflow-hidden bg-warm-900">
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-[0.06] bg-warm-500 translate-x-[30%] -translate-y-[30%]"></div>
                    <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">Available Points</p>
                    <p class="font-display text-5xl font-bold text-warm-500">@number($vm->totalPoints)</p>
                </div>
                <div class="rounded-2xl p-8 text-center bg-white border border-warm-200">
                    <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">Lifetime Earned</p>
                    <p class="font-display text-5xl font-bold text-warm-900">@number($vm->lifetimeEarned)</p>
                </div>
            </div>

            @if ($vm->tier)
                <div class="max-w-2xl mx-auto mb-10 rounded-2xl p-6 bg-white border border-warm-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="uppercase tracking-[0.2em] text-xs font-semibold text-warm-500 mb-1">Your Tier</p>
                            <p class="font-display text-2xl font-bold text-warm-900">{{ $vm->tier->getLabel() }}</p>
                        </div>
                        @if ($vm->nextTier)
                            <div class="text-right">
                                <p class="text-xs text-warm-500">Next: {{ $vm->nextTier->getLabel() }}</p>
                                <p class="text-sm font-bold text-warm-700">@number($vm->pointsToNextTier) pts to go</p>
                            </div>
                        @else
                            <p class="text-xs text-warm-500">Top tier — thanks for the love!</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Progress to next reward --}}
            @if ($vm->nextReward)
            <div class="max-w-2xl mx-auto mb-10 rounded-2xl p-6 bg-white border border-warm-200">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-warm-700">Next Reward: {{ $vm->nextReward->name }}</span>
                    <span class="text-sm font-bold text-warm-500">@number($vm->totalPoints) / @number($vm->nextReward->points_required) pts</span>
                </div>
                <div class="w-full rounded-full h-3 overflow-hidden bg-warm-200">
                    <div class="h-full rounded-full transition-all duration-700 bg-gradient-to-r from-warm-500 to-warm-400" style="width: {{ $vm->nextRewardProgressPercent() }}%;"></div>
                </div>
                <p class="text-xs mt-2 text-right text-warm-500">@number($vm->pointsToNextReward()) points to go!</p>
            </div>
            @endif

            {{-- Transaction History --}}
            @if ($vm->history->count())
            <div class="max-w-2xl mx-auto rounded-2xl overflow-hidden bg-white border border-warm-200">
                <div class="px-6 py-4 border-b border-warm-200">
                    <h3 class="font-display text-lg font-bold text-warm-900">Points History</h3>
                </div>
                <div class="divide-y divide-warm-100">
                    @foreach ($vm->history as $entry)
                    <div class="flex justify-between items-center px-6 py-4 hover:bg-warm-50 transition-colors">
                        <div>
                            <p class="font-semibold text-warm-900">{{ $entry->description }}</p>
                            <p class="text-sm text-warm-500">{{ $entry->created_at->format('M j, Y') }}</p>
                        </div>
                        <span class="font-display text-lg font-bold {{ $vm->historyEntryColorClass($entry->type) }}">
                            {{ $vm->historyEntrySign($entry->type) }}@number($entry->points)
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        @if ($vm->customerNotFound)
        <div class="max-w-xl mx-auto mb-16 rounded-2xl p-8 text-center bg-white border border-warm-200">
            <p class="text-warm-700">We couldn't find an account with that email. Points are earned automatically when your orders are delivered!</p>
        </div>
        @endif

        {{-- Available Rewards --}}
        @if ($vm->rewards->count())
        <div class="mb-16">
            <div class="text-center mb-10">
                <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">{{ $vm->content['rewards_eyebrow'] ?? 'Unlock' }}</p>
                <h2 class="font-display text-3xl font-bold text-warm-900">{{ $vm->content['rewards_heading'] ?? 'Available Rewards' }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                @foreach ($vm->rewards as $reward)
                <div @class([
                    'rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden bg-white border-2',
                    'border-warm-500' => $vm->canRedeem($reward),
                    'border-warm-200' => ! $vm->canRedeem($reward),
                ])>
                    @if ($vm->canRedeem($reward))
                    <x-storefront.pill tone="solid" size="xs" class="absolute top-3 right-3 !font-bold">Redeemable!</x-storefront.pill>
                    @endif
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-warm-100">
                        <x-heroicon-o-chat-bubble-oval-left class="w-7 h-7 text-warm-500" />
                    </div>
                    <h3 class="font-display text-lg font-bold mb-1 text-warm-900">{{ $reward->name }}</h3>
                    @if ($reward->description)
                    <p class="text-sm mb-3 text-warm-600">{{ $reward->description }}</p>
                    @endif
                    <div class="inline-block px-4 py-2 rounded-full bg-warm-100">
                        <span class="font-display font-bold text-warm-800">@number($reward->points_required)</span>
                        <span class="text-sm text-warm-500">pts</span>
                    </div>
                    <p class="text-xs mt-2 text-warm-500">{{ \App\Presenters\LoyaltyRewardPresenter::for($reward)->rewardTypeLabel() }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

{{-- How It Works --}}
<x-storefront.dark-section :show-radial="false" padding="py-20">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="uppercase tracking-[0.25em] text-xs font-semibold mb-2 text-warm-500">{{ $vm->content['how_it_works_eyebrow'] ?? 'Simple as' }}</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-white">{{ $vm->content['how_it_works_heading'] ?? 'How It Works' }}</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-10 text-center">
            @foreach ($vm->howSteps as $step)
            <div>
                <x-storefront.icon-circle size="lg" variant="subtle" class="mx-auto mb-5">
                    <x-dynamic-component :component="'heroicon-o-' . ($step['icon'] ?? 'star')" class="w-8 h-8 text-warm-500" />
                </x-storefront.icon-circle>
                <h3 class="font-display text-xl font-bold mb-2 text-warm-200">{{ $step['title'] }}</h3>
                <p class="text-warm-500">{{ $step['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-storefront.dark-section>
@endif
</x-layouts.storefront>
