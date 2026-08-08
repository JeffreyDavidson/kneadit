<x-filament-panels::page>
    <div class="mx-auto max-w-[1100px]">
        {{-- Current Plan Banner --}}
        <div class="from-brand-600 via-brand-500 to-brand-300 mb-8 flex items-center gap-4 rounded-2xl bg-gradient-to-br px-8 py-6 text-white shadow-lg">
            <div class="flex items-center justify-center rounded-xl bg-white/20 p-3">
                <x-heroicon-o-cake class="h-7 w-7" />
            </div>
            <div>
                <p class="m-0 text-[0.85rem] opacity-85">Your current plan</p>
                <h2 class="m-0 mt-1 text-2xl font-bold">
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
                            <span class="absolute top-3 right-3 rounded-full bg-white/20 px-2.5 py-1 text-[0.7rem] font-semibold tracking-wider text-white uppercase">Current</span>
                        @elseif ($key === 'pro')
                            <span class="absolute top-3 right-3 rounded-full bg-white/20 px-2.5 py-1 text-[0.7rem] font-semibold tracking-wider text-white uppercase">Best Value</span>
                        @endif
                        <h3 class="m-0 text-[1.1rem] font-semibold opacity-90">{{ $plan['name'] }}</h3>
                        <div class="mt-2">
                            <span class="text-[2.5rem] leading-none font-extrabold">${{ $plan['price'] }}</span>
                            <span class="text-[0.9rem] opacity-70">/month</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="flex flex-1 flex-col p-6">
                        <ul class="m-0 flex-1 list-none p-0">
                            @foreach ($plan['features'] as $feature)
                                <li @class([
                                    'flex items-start gap-2.5 py-2',
                                    'border-b border-brand-200/40' => ! $loop->last,
                                ])>
                                    @if (str_contains($feature, 'Everything in'))
                                        <x-heroicon-s-arrow-up class="text-honey mt-0.5 h-4 w-4 flex-shrink-0" />
                                        <span class="text-brand-600 text-sm font-semibold">{{ $feature }}</span>
                                    @else
                                        <x-heroicon-s-check class="mt-0.5 h-4 w-4 flex-shrink-0 text-emerald-500" />
                                        <span class="text-walnut text-sm">{{ $feature }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Action Button --}}
                        <div class="mt-5">
                            @if ($isUpgrade)
                                <button
                                    wire:click="redirectToBilling"
                                    class="w-full px-4 py-3 rounded-xl font-bold text-[0.9rem] text-center cursor-pointer border-0 text-white bg-gradient-to-br {{ $key === 'pro' ? 'from-brand-900 to-brand-700' : 'from-honey to-golden' }} shadow-md hover:-translate-y-px transition-all"
                                >
                                    Upgrade to {{ $plan['name'] }}
                                </button>
                            @elseif ($isCurrent)
                                <div class="bg-brand-300/15 text-brand-600 border-brand-300/30 w-full rounded-xl border px-4 py-3 text-center text-[0.9rem] font-semibold">
                                    Your Current Plan
                                </div>
                            @else
                                <div class="bg-brand-50 text-brand-500 w-full rounded-xl px-4 py-3 text-center text-[0.9rem] font-semibold">
                                    Included in your plan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div class="text-brand-600 mt-8 text-center text-[0.85rem]">
            <p class="m-0">Need help choosing? Reach out to us anytime.</p>
            <p class="m-0 mt-1.5 opacity-70">All plans include a 30-day free trial. Cancel anytime.</p>
        </div>
    </div>
</x-filament-panels::page>
