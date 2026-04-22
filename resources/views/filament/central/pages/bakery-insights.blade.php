<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div class="flex gap-2 mb-6">
        @foreach ([
            'health' => 'Health Scores',
            'churn' => 'Churn Alerts',
            'upgrade' => 'Upgrade Triggers',
        ] as $key => $label)
            <button wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-honey/25 cursor-pointer',
                    'bg-honey text-warm-black' => $activeTab === $key,
                    'bg-transparent text-honey' => $activeTab !== $key,
                ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Health Scores Tab --}}
    @if ($activeTab === 'health')
        @php
            $stats = $this->getHealthSummaryStats();
            $tenants = $this->getTenantHealthData();
        @endphp

        <div class="grid grid-cols-4 gap-4 mb-6">
            <x-central.stat-card label="Average Health" value-class="text-[1.75rem] text-white">{{ $stats['average'] }}</x-central.stat-card>
            <x-central.stat-card label="Healthy (>70)" value-class="text-[1.75rem] text-emerald-500">{{ $stats['healthy'] }}</x-central.stat-card>
            <x-central.stat-card label="At Risk (40-70)" value-class="text-[1.75rem] text-amber-500">{{ $stats['at_risk'] }}</x-central.stat-card>
            <x-central.stat-card label="Critical (<40)" value-class="text-[1.75rem] text-red-500">{{ $stats['critical'] }}</x-central.stat-card>
        </div>

        <div class="grid grid-cols-[repeat(auto-fill,minmax(340px,1fr))] gap-4">
            @foreach ($tenants as $tenant)
                @php
                    $score = $tenant['health_score'];
                    $textClass = $score > 70 ? 'text-emerald-500' : ($score >= 40 ? 'text-amber-500' : 'text-red-500');
                    $borderClass = $score > 70 ? 'border-l-emerald-500' : ($score >= 40 ? 'border-l-amber-500' : 'border-l-red-500');
                    $bgClass = $score > 70 ? 'bg-emerald-500' : ($score >= 40 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <x-central.card class="transition-transform border-l-4 {{ $borderClass }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="font-bold text-white text-base">{{ $tenant['name'] }}</div>
                            <div class="text-cinnamon text-[0.8rem]">{{ $tenant['owner'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[1.75rem] font-bold leading-none {{ $textClass }}">{{ $score }}</div>
                            <div class="text-[0.7rem] text-cinnamon">/100</div>
                        </div>
                    </div>

                    <x-central.badge color="honey-soft" class="mb-4 border border-honey/25">{{ $tenant['plan'] }}</x-central.badge>

                    @php
                        $factors = [
                            ['label' => 'Login Recency', 'value' => $tenant['login_score'], 'max' => 25],
                            ['label' => 'Orders', 'value' => $tenant['order_score'], 'max' => 25],
                            ['label' => 'Products', 'value' => $tenant['product_score'], 'max' => 20],
                            ['label' => 'Setup', 'value' => $tenant['setup_score'], 'max' => 30],
                        ];
                    @endphp
                    @foreach ($factors as $factor)
                        <div class="mb-2">
                            <div class="flex justify-between text-xs text-cinnamon mb-0.5">
                                <span>{{ $factor['label'] }}</span>
                                <span>{{ $factor['value'] }}/{{ $factor['max'] }}</span>
                            </div>
                            <div class="bg-espresso rounded h-2 overflow-hidden">
                                <div class="h-full rounded {{ $bgClass }}" style="width: {{ $factor['max'] > 0 ? round(($factor['value'] / $factor['max']) * 100) : 0 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </x-central.card>
            @endforeach
        </div>
    @endif

    {{-- Churn Alerts Tab --}}
    @if ($activeTab === 'churn')
        @php $alerts = $this->getAlerts(); @endphp

        @if ($alerts->isEmpty())
            <x-central.card padding="py-16 px-8" class="text-center">
                <div class="mb-4">
                    <x-heroicon-o-check-circle class="w-12 h-12 inline-block text-emerald-500" />
                </div>
                <div class="text-[1.25rem] font-bold text-emerald-500">All bakeries are healthy!</div>
                <div class="text-cinnamon mt-2">No churn alerts at this time.</div>
            </x-central.card>
        @else
            <div class="grid gap-4">
                @foreach ($alerts as $alert)
                    @php
                        $borderClass = $alert['severity'] === 'critical' ? 'border-l-red-500' : 'border-l-amber-500';
                        $typeBadgeClasses = [
                            'trial_expiring' => 'bg-red-500/15 text-red-500',
                            'no_login' => 'bg-amber-500/15 text-amber-500',
                            'no_orders' => 'bg-amber-500/15 text-amber-500',
                            'low_health' => 'bg-red-500/15 text-red-500',
                        ];
                        $defaultTypeClass = $alert['severity'] === 'critical' ? 'bg-red-500/15 text-red-500' : 'bg-amber-500/15 text-amber-500';
                        $typeClass = $typeBadgeClasses[$alert['type']] ?? $defaultTypeClass;
                    @endphp
                    <x-central.card class="border-l-4 {{ $borderClass }}">
                        <div class="flex justify-between items-start flex-wrap gap-3">
                            <div class="flex-1 min-w-[200px]">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-bold text-white text-base">{{ $alert['name'] }}</span>
                                    <span class="inline-block rounded-full px-2.5 py-1 text-[0.7rem] font-semibold {{ $typeClass }}">
                                        {{ $alert['type_label'] }}
                                    </span>
                                </div>
                                <div class="text-parchment text-[0.85rem]">{{ $alert['description'] }}</div>
                                <div class="text-cinnamon text-xs mt-1.5">Signed up {{ $alert['days_since_signup'] }} days ago</div>
                            </div>
                            <div class="flex gap-2 items-center flex-shrink-0">
                                @if (in_array($alert['tenant_id'], $this->extendedTrials))
                                    <x-central.badge color="success" :uppercase="false">Extended</x-central.badge>
                                @else
                                    <x-central.button variant="secondary" wire:click="extendTrial('{{ $alert['tenant_id'] }}')" wire:loading.attr="disabled">Extend Trial</x-central.button>
                                @endif
                                @if (in_array($alert['tenant_id'], $this->sentNudges))
                                    <x-central.badge color="success" :uppercase="false">Nudge Sent</x-central.badge>
                                @else
                                    <x-central.button variant="secondary" wire:click="sendNudge('{{ $alert['tenant_id'] }}')" wire:loading.attr="disabled">Send Nudge</x-central.button>
                                @endif
                                <x-central.button :href="$this->getViewTenantUrl($alert['tenant_id'])">View Tenant</x-central.button>
                            </div>
                        </div>
                    </x-central.card>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Upgrade Triggers Tab --}}
    @if ($activeTab === 'upgrade')
        @php $tenants = $this->getTenantUsageData(); @endphp

        @if ($tenants->isEmpty())
            <x-central.card padding="p-12" class="text-center">
                <div class="mb-4">
                    <x-heroicon-o-check-circle class="w-12 h-12 inline-block text-emerald-500" />
                </div>
                <div class="text-emerald-500 font-bold text-base mb-2">All Tenants Within Limits</div>
                <p class="text-cinnamon max-w-[480px] mx-auto">
                    No bakeries are currently approaching their plan limits. When tenants reach 80% or more of their product or order limits, they'll appear here as upgrade candidates.
                </p>
            </x-central.card>
        @else
            <div class="mb-4">
                <p class="text-cinnamon text-sm">
                    <span class="text-honey font-bold">{{ $tenants->count() }}</span> tenant{{ $tenants->count() !== 1 ? 's' : '' }} approaching or at plan limits
                </p>
            </div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(380px,1fr))] gap-4">
                @foreach ($tenants as $t)
                    <x-central.card :class="$t['at_limit'] ? 'border-red-500' : 'border-amber-500/30'" class="transition-transform">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-white text-base font-bold mb-0.5">{{ $t['name'] }}</div>
                                <span class="text-cinnamon text-xs">{{ $t['plan'] }} Plan</span>
                            </div>
                            @if ($t['at_limit'])
                                <x-central.badge color="danger" :uppercase="false">At Limit</x-central.badge>
                            @else
                                <x-central.badge color="warning" :uppercase="false">Approaching</x-central.badge>
                            @endif
                        </div>

                        @php
                            $pBarClass = $t['product_percent'] >= 100 ? 'bg-red-500' : ($t['product_percent'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500');
                            $oBarClass = $t['order_percent'] >= 100 ? 'bg-red-500' : ($t['order_percent'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500');
                        @endphp
                        <div class="mb-3">
                            <div class="flex justify-between mb-1">
                                <x-central.eyebrow as="span">Products</x-central.eyebrow>
                                <span class="text-parchment text-xs font-semibold">{{ $t['product_count'] }} / {{ $t['product_limit'] }}</span>
                            </div>
                            <div class="bg-espresso rounded h-2 overflow-hidden">
                                <div class="h-full rounded transition-all duration-300 {{ $pBarClass }}" style="width: {{ min($t['product_percent'], 100) }}%;"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <x-central.eyebrow as="span">Orders This Month</x-central.eyebrow>
                                <span class="text-parchment text-xs font-semibold">{{ $t['order_count'] }} / {{ $t['order_limit'] }}</span>
                            </div>
                            <div class="bg-espresso rounded h-2 overflow-hidden">
                                <div class="h-full rounded transition-all duration-300 {{ $oBarClass }}" style="width: {{ min($t['order_percent'], 100) }}%;"></div>
                            </div>
                        </div>

                        <div class="border-t border-honey/8 pt-4">
                            @php $nextPlan = $this->getNextPlan($t['plan_key']); @endphp
                            @if ($nextPlan)
                                <x-central.button wire:click="suggestUpgrade('{{ $t['tenant']->id }}')" class="w-full">
                                    Suggest Upgrade to {{ $nextPlan }}
                                </x-central.button>
                            @endif
                        </div>
                    </x-central.card>
                @endforeach
            </div>
        @endif
    @endif
</x-filament-panels::page>
