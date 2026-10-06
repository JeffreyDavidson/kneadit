<x-filament-panels::page>
    <div class="mx-auto max-w-[1100px]">
        {{-- Current Plan Banner --}}
        <div class="mb-8 flex items-center gap-4 rounded-2xl bg-(--kn-espresso) px-8 py-6 text-(--kn-on-espresso) shadow-lg">
            <div class="flex items-center justify-center rounded-xl bg-(--kn-on-espresso)/15 p-3">
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
                @endphp
                <div @class([
                    'rounded-2xl bg-(--kn-surface) flex flex-col overflow-hidden',
                    'border-2 border-(--kn-honey) shadow-lg scale-[1.02]' => $isCurrent,
                    'border border-brand-300/25 shadow-sm' => ! $isCurrent,
                ])>
                    {{-- Card Header --}}
                    <div class="relative bg-(--kn-espresso) p-6 text-(--kn-on-espresso)">
                        @if ($isCurrent)
                            <span class="absolute top-3 right-3 rounded-full bg-(--kn-on-espresso)/15 px-2.5 py-1 text-[0.7rem] font-semibold tracking-wider text-(--kn-on-espresso) uppercase">Current</span>
                        @elseif ($key === 'pro')
                            <span class="absolute top-3 right-3 rounded-full bg-(--kn-on-espresso)/15 px-2.5 py-1 text-[0.7rem] font-semibold tracking-wider text-(--kn-on-espresso) uppercase">Best Value</span>
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
                                        <x-heroicon-s-arrow-up class="mt-0.5 h-4 w-4 flex-shrink-0 text-(--kn-honey-text)" />
                                        <span class="text-sm font-semibold text-(--kn-muted)">{{ $feature }}</span>
                                    @else
                                        <x-heroicon-s-check class="mt-0.5 h-4 w-4 flex-shrink-0 text-(--kn-success)" />
                                        <span class="text-walnut text-sm">{{ $feature }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Action Button --}}
                        <div class="mt-5">
                            @if ($isUpgrade && ! $this->canManageBilling())
                                <div class="w-full rounded-xl bg-(--kn-surface-sunken) px-4 py-3 text-center text-[0.9rem] font-semibold text-(--kn-muted)">
                                    Ask your bakery owner to upgrade
                                </div>
                            @elseif ($isUpgrade)
                                <button
                                    wire:click="mountAction('manageBilling')"
                                    class="w-full cursor-pointer rounded-xl border-0 bg-(--kn-honey) px-4 py-3 text-center text-[0.9rem] font-bold text-(--kn-on-honey) shadow-md transition-all hover:-translate-y-px hover:bg-(--kn-honey-hover)"
                                >
                                    Upgrade to {{ $plan['name'] }}
                                </button>
                            @elseif ($isCurrent)
                                <div class="bg-brand-300/15 border-brand-300/30 w-full rounded-xl border px-4 py-3 text-center text-[0.9rem] font-semibold text-(--kn-muted)">
                                    Your Current Plan
                                </div>
                            @else
                                <div class="w-full rounded-xl bg-(--kn-surface-sunken) px-4 py-3 text-center text-[0.9rem] font-semibold text-(--kn-muted)">
                                    Included in your plan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div class="mt-8 text-center text-[0.85rem] text-(--kn-muted)">
            <p class="m-0">Need help choosing? Reach out to us anytime.</p>
            <p class="m-0 mt-1.5 opacity-70">All plans include a 30-day free trial. Cancel anytime.</p>
        </div>
    </div>
</x-filament-panels::page>
