<x-filament-panels::page>
    <div class="max-w-5xl mx-auto">
        {{-- Current Plan Banner --}}
        <div class="rounded-xl bg-gradient-to-r from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 p-6 mb-8 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:32px;height:32px;color:#d97706;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53.53.375.375 0 0 1 .53-.53Zm0 0L12 3.375m.265-.265A.375.375 0 0 0 12 3v0m3.265.11a.375.375 0 1 1-.53.53.375.375 0 0 1 .53-.53Zm0 0L15 3.375m.265-.265A.375.375 0 0 0 15 3v0m-6 .265a.375.375 0 1 1-.53-.53.375.375 0 0 1 .53.53Zm0 0L9 3.375m-.265.265A.375.375 0 0 0 9 3v0" /></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;color:#f59e0b;flex-shrink:0;margin-top:2px;"><path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06l-6.22-6.22V21a.75.75 0 0 1-1.5 0V4.81l-6.22 6.22a.75.75 0 1 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" /></svg>
                                    <span class="font-medium text-amber-700 dark:text-amber-300">{{ $feature }}</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;color:#22c55e;flex-shrink:0;margin-top:2px;"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" /></svg>
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
