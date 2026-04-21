<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        @foreach ([
            'health' => 'Health Scores',
            'churn' => 'Churn Alerts',
            'upgrade' => 'Upgrade Triggers',
        ] as $key => $label)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                style="padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(212,146,12,0.25); cursor: pointer;
                    {{ $activeTab === $key ? 'background: #d4920c; color: #1c1410;' : 'background: transparent; color: #d4920c;' }}"
            >
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
            <x-central.stat-card label="At Risk (40–70)" value-class="text-[1.75rem] text-amber-500">{{ $stats['at_risk'] }}</x-central.stat-card>
            <x-central.stat-card label="Critical (<40)" value-class="text-[1.75rem] text-red-500">{{ $stats['critical'] }}</x-central.stat-card>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem;">
            @foreach ($tenants as $tenant)
                @php
                    $score = $tenant['health_score'];
                    $color = $score > 70 ? '#10b981' : ($score >= 40 ? '#f59e0b' : '#ef4444');
                @endphp
                <x-central.card style="border-left: 4px solid {{ $color }};" class="transition-transform">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <div style="font-weight: 700; color: white; font-size: 1rem;">{{ $tenant['name'] }}</div>
                            <div style="color: #8b6844; font-size: 0.8rem;">{{ $tenant['owner'] }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: {{ $color }}; line-height: 1;">{{ $score }}</div>
                            <div style="font-size: 0.7rem; color: #8b6844;">/100</div>
                        </div>
                    </div>

                    <span style="display: inline-block; background: rgba(212,146,12,0.1); color: #d4920c; border: 1px solid rgba(212,146,12,0.25); border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; margin-bottom: 1rem;">
                        {{ $tenant['plan'] }}
                    </span>

                    @php
                        $factors = [
                            ['label' => 'Login Recency', 'value' => $tenant['login_score'], 'max' => 25],
                            ['label' => 'Orders', 'value' => $tenant['order_score'], 'max' => 25],
                            ['label' => 'Products', 'value' => $tenant['product_score'], 'max' => 20],
                            ['label' => 'Setup', 'value' => $tenant['setup_score'], 'max' => 30],
                        ];
                    @endphp
                    @foreach ($factors as $factor)
                        <div style="margin-bottom: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #8b6844; margin-bottom: 0.2rem;">
                                <span>{{ $factor['label'] }}</span>
                                <span>{{ $factor['value'] }}/{{ $factor['max'] }}</span>
                            </div>
                            <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                                <div style="height: 100%; border-radius: 4px; background: {{ $color }}; width: {{ $factor['max'] > 0 ? round(($factor['value'] / $factor['max']) * 100) : 0 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 inline-block"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="text-[1.25rem] font-bold text-emerald-500">All bakeries are healthy!</div>
                <div class="text-cinnamon mt-2">No churn alerts at this time.</div>
            </x-central.card>
        @else
            <div style="display: grid; gap: 1rem;">
                @foreach ($alerts as $alert)
                    @php
                        $badgeColor = $alert['severity'] === 'critical' ? '#ef4444' : '#f59e0b';
                        $badgeBg = $alert['severity'] === 'critical' ? 'rgba(239,68,68,0.15)' : 'rgba(245,158,11,0.15)';
                        $typeBadgeColors = [
                            'trial_expiring' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444'],
                            'no_login' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#f59e0b'],
                            'no_orders' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#f59e0b'],
                            'low_health' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444'],
                        ];
                        $tb = $typeBadgeColors[$alert['type']] ?? ['bg' => $badgeBg, 'color' => $badgeColor];
                    @endphp
                    <x-central.card style="border-left: 4px solid {{ $badgeColor }};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                            <div style="flex: 1; min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 700; color: white; font-size: 1rem;">{{ $alert['name'] }}</span>
                                    <span style="display: inline-block; background: {{ $tb['bg'] }}; color: {{ $tb['color'] }}; border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600;">
                                        {{ $alert['type_label'] }}
                                    </span>
                                </div>
                                <div style="color: #faf0d6; font-size: 0.85rem;">{{ $alert['description'] }}</div>
                                <div style="color: #8b6844; font-size: 0.75rem; margin-top: 0.35rem;">Signed up {{ $alert['days_since_signup'] }} days ago</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-shrink: 0;">
                                @if (in_array($alert['tenant_id'], $this->extendedTrials))
                                    <span style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.25); border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600;">Extended</span>
                                @else
                                    <button wire:click="extendTrial('{{ $alert['tenant_id'] }}')" wire:loading.attr="disabled" style="background: #1c1410; color: #d4920c; border: 1px solid rgba(212,146,12,0.25); border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Extend Trial</button>
                                @endif
                                @if (in_array($alert['tenant_id'], $this->sentNudges))
                                    <span style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.25); border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600;">Nudge Sent</span>
                                @else
                                    <button wire:click="sendNudge('{{ $alert['tenant_id'] }}')" wire:loading.attr="disabled" style="background: #1c1410; color: #e8b04a; border: 1px solid rgba(232,176,74,0.25); border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Send Nudge</button>
                                @endif
                                <a href="{{ $this->getViewTenantUrl($alert['tenant_id']) }}" class="bg-honey text-warm-black border-0 rounded-lg px-3 py-2 text-xs font-bold cursor-pointer no-underline inline-block">View Tenant</a>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 inline-block"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="text-emerald-500 font-bold text-base mb-2">All Tenants Within Limits</div>
                <p class="text-cinnamon max-w-[480px] mx-auto">
                    No bakeries are currently approaching their plan limits. When tenants reach 80% or more of their product or order limits, they'll appear here as upgrade candidates.
                </p>
            </x-central.card>
        @else
            <div style="margin-bottom: 1rem;">
                <p style="color: #8b6844; font-size: 0.875rem;">
                    <span style="color: #d4920c; font-weight: 700;">{{ $tenants->count() }}</span> tenant{{ $tenants->count() !== 1 ? 's' : '' }} approaching or at plan limits
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1rem;">
                @foreach ($tenants as $t)
                    <x-central.card :class="$t['at_limit'] ? 'border-red-500' : 'border-amber-500/30'" class="transition-transform">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                            <div>
                                <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.15rem;">{{ $t['name'] }}</div>
                                <span style="color: #8b6844; font-size: 0.75rem;">{{ $t['plan'] }} Plan</span>
                            </div>
                            @if ($t['at_limit'])
                                <x-central.badge color="danger" :uppercase="false">At Limit</x-central.badge>
                            @else
                                <x-central.badge color="warning" :uppercase="false">Approaching</x-central.badge>
                            @endif
                        </div>

                        <div style="margin-bottom: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <x-central.eyebrow as="span">Products</x-central.eyebrow>
                                <span style="color: #faf0d6; font-size: 0.75rem; font-weight: 600;">{{ $t['product_count'] }} / {{ $t['product_limit'] }}</span>
                            </div>
                            <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                                @php $pColor = $t['product_percent'] >= 100 ? '#ef4444' : ($t['product_percent'] >= 80 ? '#f59e0b' : '#10b981'); @endphp
                                <div style="width: {{ min($t['product_percent'], 100) }}%; background: {{ $pColor }}; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <x-central.eyebrow as="span">Orders This Month</x-central.eyebrow>
                                <span style="color: #faf0d6; font-size: 0.75rem; font-weight: 600;">{{ $t['order_count'] }} / {{ $t['order_limit'] }}</span>
                            </div>
                            <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                                @php $oColor = $t['order_percent'] >= 100 ? '#ef4444' : ($t['order_percent'] >= 80 ? '#f59e0b' : '#10b981'); @endphp
                                <div style="width: {{ min($t['order_percent'], 100) }}%; background: {{ $oColor }}; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
                            </div>
                        </div>

                        <div style="border-top: 1px solid rgba(212,146,12,0.08); padding-top: 1rem;">
                            @php $nextPlan = $this->getNextPlan($t['plan_key']); @endphp
                            @if ($nextPlan)
                                <button
                                    wire:click="suggestUpgrade('{{ $t['tenant']->id }}')"
                                    style="width: 100%; background: #d4920c; color: #1c1410; border: none; border-radius: 8px; padding: 0.6rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;"
                                >
                                    Suggest Upgrade to {{ $nextPlan }}
                                </button>
                            @endif
                        </div>
                    </x-central.card>
                @endforeach
            </div>
        @endif
    @endif
</x-filament-panels::page>
