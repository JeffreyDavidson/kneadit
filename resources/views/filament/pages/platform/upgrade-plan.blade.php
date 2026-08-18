<x-filament-panels::page>
    <div class="max-w-[1100px] mx-auto">
        {{-- Current Plan Banner --}}
        <div class="bg-gradient-to-br from-brand-600 via-brand-500 to-brand-300 rounded-2xl px-8 py-6 mb-8 text-white flex items-center gap-4 shadow-lg">
            <div class="bg-white/20 rounded-xl p-3 flex items-center justify-center">
                <x-heroicon-o-cake class="w-7 h-7" />
            </div>
            <div>
                <p class="m-0 text-[0.85rem] opacity-85">Your current plan</p>
                <h2 class="mt-1 m-0 text-2xl font-bold">
                    {{ $plans[$currentPlan]['name'] ?? 'Starter' }} — ${{ $plans[$currentPlan]['price'] ?? 9 }}/mo
                </h2>
            </div>
        </div>

        {{-- Plan Cards --}}
        <div class="grid grid-cols-3 gap-6">
            @foreach ($plans as $key => $plan)
                @php
                    $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];
                    $isCurrent = $key === $currentPlan;
                    $isUpgrade = $hierarchy[$key] > $hierarchy[$currentPlan];
                    $headerGradient = match ($key) {
                        'starter' => 'from-brand-700 to-brand-600',
                        'growth' => 'from-brand-600 to-honey',
                        'pro' => 'from-brand-900 to-brand-700',
                    };
                @endphp
                <div @class([
                    'rounded-2xl bg-white flex flex-col overflow-hidden',
                    'border-2 border-honey shadow-lg scale-[1.02]' => $isCurrent,
                    'border border-brand-300/25 shadow-sm' => ! $isCurrent,
                ])>
                    {{-- Card Header --}}
                    <div class="p-6 text-white relative bg-gradient-to-br {{ $headerGradient }}">
                        @if ($isCurrent)
                            <span class="absolute top-3 right-3 bg-white/20 text-white text-[0.7rem] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider">Current</span>
                        @elseif ($key === 'pro')
                            <span class="absolute top-3 right-3 bg-white/20 text-white text-[0.7rem] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider">Best Value</span>
                        @endif
                        <h3 class="m-0 text-[1.1rem] font-semibold opacity-90">{{ $plan['name'] }}</h3>
                        <div class="mt-2">
                            <span class="text-[2.5rem] font-extrabold leading-none">${{ $plan['price'] }}</span>
                            <span class="text-[0.9rem] opacity-70">/month</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="p-6 flex-1 flex flex-col">
                        <ul class="list-none m-0 p-0 flex-1">
                            @foreach ($plan['features'] as $feature)
                                <li @class([
                                    'flex items-start gap-2.5 py-2',
                                    'border-b border-brand-200/40' => ! $loop->last,
                                ])>
                                    @if (str_contains($feature, 'Everything in'))
                                        <x-heroicon-s-arrow-up class="w-4 h-4 text-honey flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-brand-600 font-semibold">{{ $feature }}</span>
                                    @else
                                        <x-heroicon-s-check class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-walnut">{{ $feature }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Action Button --}}
                        <div class="mt-5">
                            @if ($isUpgrade)
                                <button wire:click="redirectToBilling"
                                    class="w-full px-4 py-3 rounded-xl font-bold text-[0.9rem] text-center cursor-pointer border-0 text-white bg-gradient-to-br {{ $key === 'pro' ? 'from-brand-900 to-brand-700' : 'from-honey to-golden' }} shadow-md hover:-translate-y-px transition-all">
                                    Upgrade to {{ $plan['name'] }}
                                </button>
                            @elseif ($isCurrent)
                                <div class="w-full px-4 py-3 rounded-xl font-semibold text-[0.9rem] text-center bg-brand-300/15 text-brand-600 border border-brand-300/30">
                                    Your Current Plan
                                </div>
                            @else
                                <div class="w-full px-4 py-3 rounded-xl font-semibold text-[0.9rem] text-center bg-brand-50 text-brand-500">
                                    Included in your plan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div class="mt-8 text-center text-[0.85rem] text-brand-600">
            <p class="m-0">Need help choosing? Reach out to us anytime.</p>
            <p class="mt-1.5 m-0 opacity-70">All plans include a 30-day free trial. Cancel anytime.</p>
        </div>
    </div>
</x-filament-panels::page>
