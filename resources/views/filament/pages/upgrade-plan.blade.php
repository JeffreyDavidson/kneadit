<x-filament-panels::page>
    <div class="max-w-5xl mx-auto">
        {{-- Current Plan Banner --}}
        <div class="rounded-xl bg-gradient-to-r from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 p-6 mb-8 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <x-heroicon-o-cake class="w-8 h-8 text-amber-600" />
                <div>
                    <p class="text-sm text-amber-700 dark:text-amber-300">Your current plan</p>
                    <h2 class="text-2xl font-bold text-amber-900 dark:text-amber-100">
                        {{ $plans[$currentPlan]['name'] ?? 'Starter' }} — ${{ $plans[$currentPlan]['price'] ?? 9 }}/mo
                    </h2>
                </div>
            </div>
        </div>

        {{-- Plan Cards --}}
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ($plans as $key => $plan)
                @php
                    $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];
                    $isCurrent = $key === $currentPlan;
                    $isUpgrade = $hierarchy[$key] > $hierarchy[$currentPlan];
                    $isDowngrade = $hierarchy[$key] < $hierarchy[$currentPlan];
                @endphp
                <div @class([
                    'rounded-xl border-2 p-6 flex flex-col',
                    'border-amber-500 bg-amber-50 dark:bg-amber-900/20 ring-2 ring-amber-500/20' => $isCurrent,
                    'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' => !$isCurrent,
                ])>
                    @if ($isCurrent)
                        <span class="inline-flex items-center self-start px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500 text-white mb-3">
                            Current Plan
                        </span>
                    @elseif ($key === 'pro')
                        <span class="inline-flex items-center self-start px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500 text-white mb-3">
                            Most Popular
                        </span>
                    @else
                        <div class="mb-3 h-5"></div>
                    @endif

                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                    <div class="mt-2 mb-4">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ $plan['price'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/month</span>
                    </div>

                    <ul class="space-y-2 mb-6 flex-1">
                        @foreach ($plan['features'] as $feature)
                            <li class="flex items-start gap-2 text-sm">
                                @if (str_contains($feature, 'Everything in'))
                                    <x-heroicon-s-arrow-up class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" />
                                    <span class="font-medium text-amber-700 dark:text-amber-300">{{ $feature }}</span>
                                @else
                                    <x-heroicon-s-check class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                                    <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($isUpgrade)
                        <button
                            wire:click="redirectToBilling"
                            @class([
                                'w-full py-2.5 px-4 rounded-lg font-semibold text-center transition-colors',
                                'bg-amber-500 hover:bg-amber-600 text-white' => $key === 'growth',
                                'bg-purple-600 hover:bg-purple-700 text-white' => $key === 'pro',
                            ])
                        >
                            Upgrade to {{ $plan['name'] }} 🚀
                        </button>
                    @elseif ($isCurrent)
                        <div class="w-full py-2.5 px-4 rounded-lg font-semibold text-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                            Your Plan ✓
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>Need help choosing? Reach out to us anytime — we're bakers too! 🍞</p>
            <p class="mt-1">All plans include a 14-day free trial. Cancel anytime.</p>
        </div>
    </div>
</x-filament-panels::page>
