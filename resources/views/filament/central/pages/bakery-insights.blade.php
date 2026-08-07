<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div class="mb-6 flex gap-2">
        @foreach ([
            'health' => 'Health Scores',
            'churn' => 'Churn Alerts',
            'upgrade' => 'Upgrade Triggers',
        ] as $key => $label)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-5 py-2 rounded-lg text-[0.8rem] font-bold border border-honey/25 cursor-pointer',
                    'bg-honey text-warm-black' => $activeTab === $key,
                    'bg-transparent text-honey' => $activeTab !== $key,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Health Scores Tab --}}
    @if ($activeTab === 'health')
        @php
            $stats = $this->getHealthSummaryStats();
            $tenants = $this->getTenantHealthData()->sortBy('health_score')->values();
            $total = max(1, (int) $stats['healthy'] + (int) $stats['at_risk'] + (int) $stats['critical']);
            $avg = (int) $stats['average'];
            $avgClass = $avg > 70 ? 'text-emerald-400' : ($avg >= 40 ? 'text-amber-400' : 'text-red-400');
            $avgDotClass = $avg > 70 ? 'bg-emerald-500' : ($avg >= 40 ? 'bg-amber-500' : 'bg-red-500');
        @endphp

        {{-- Overview row: hero average + segmented distribution --}}
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
            <x-central.card>
                <div class="mb-2 flex items-start justify-between">
                    <x-central.eyebrow>Average Health</x-central.eyebrow>
                    <span class="text-cinnamon inline-flex items-center gap-1.5 text-[0.7rem]">
                        <span class="w-1.5 h-1.5 rounded-full {{ $avgDotClass }}"></span>
                        {{ $avg > 70 ? 'Healthy' : ($avg >= 40 ? 'At risk' : 'Critical') }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[2.5rem] font-bold leading-none {{ $avgClass }}">{{ $avg }}</span>
                    <span class="text-cinnamon text-[0.8rem] font-semibold">/ 100</span>
                </div>
                <p class="text-cinnamon mt-2 text-[0.75rem]">
                    Across {{ $total }} active {{ Illuminate\Support\Str::plural('bakery', $total) }}.
                </p>
            </x-central.card>

            <x-central.card>
                <div class="mb-3 flex items-center justify-between">
                    <x-central.eyebrow>Distribution</x-central.eyebrow>
                    <div class="flex items-center gap-4 text-[0.75rem]">
                        <span class="text-parchment inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Healthy &gt; 70
                        </span>
                        <span class="text-parchment inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            At risk 40-70
                        </span>
                        <span class="text-parchment inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            Critical &lt; 40
                        </span>
                    </div>
                </div>

                <div class="bg-espresso flex h-2 overflow-hidden rounded-full">
                    <div
                        class="h-full bg-emerald-500"
                        style="width: {{ round(($stats['healthy'] / $total) * 100, 2) }}%;"
                    ></div>
                    <div
                        class="h-full bg-amber-500"
                        style="width: {{ round(($stats['at_risk'] / $total) * 100, 2) }}%;"
                    ></div>
                    <div
                        class="h-full bg-red-500"
                        style="width: {{ round(($stats['critical'] / $total) * 100, 2) }}%;"
                    ></div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-3">
                    <div>
                        <div class="text-[1.5rem] leading-none font-bold text-emerald-400">{{ $stats['healthy'] }}</div>
                        <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">Healthy</div>
                    </div>
                    <div>
                        <div class="text-[1.5rem] leading-none font-bold text-amber-400">{{ $stats['at_risk'] }}</div>
                        <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">At risk</div>
                    </div>
                    <div>
                        <div class="text-[1.5rem] leading-none font-bold text-red-400">{{ $stats['critical'] }}</div>
                        <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">Critical</div>
                    </div>
                </div>
            </x-central.card>
        </div>

        @if ($tenants->isEmpty())
            <x-central.card padding="py-16 px-8" class="text-center">
                <x-heroicon-o-check-circle class="mb-3 inline-block h-10 w-10 text-emerald-500" />
                <div class="font-semibold text-white">No bakeries to evaluate yet.</div>
            </x-central.card>
        @else
            <div class="mb-3 flex items-center justify-between">
                <x-central.eyebrow>Bakeries (most at risk first)</x-central.eyebrow>
                <span class="text-cinnamon text-[0.75rem]">{{ $tenants->count() }} total</span>
            </div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-4">
                @foreach ($tenants as $tenant)
                    @php
                        $score = (int) $tenant['health_score'];
                        $severity = $score > 70 ? 'healthy' : ($score >= 40 ? 'risk' : 'critical');
                        $ring = match ($severity) {
                            'healthy' => ['text' => 'text-emerald-400', 'stroke' => 'stroke-emerald-500', 'tint' => 'bg-emerald-500/5', 'border' => 'border-emerald-500/20'],
                            'risk' => ['text' => 'text-amber-400', 'stroke' => 'stroke-amber-500', 'tint' => 'bg-amber-500/5', 'border' => 'border-amber-500/20'],
                            'critical' => ['text' => 'text-red-400', 'stroke' => 'stroke-red-500', 'tint' => 'bg-red-500/5', 'border' => 'border-red-500/25'],
                        };
                        // Ring circumference for r=20: 2 * pi * 20 ≈ 125.66
                        $circumference = 125.66;
                        $offset = $circumference * (1 - min(100, max(0, $score)) / 100);
                        $factors = [
                            ['label' => 'Logins', 'value' => $tenant['login_score'], 'max' => 25],
                            ['label' => 'Orders', 'value' => $tenant['order_score'], 'max' => 25],
                            ['label' => 'Products', 'value' => $tenant['product_score'], 'max' => 20],
                            ['label' => 'Setup', 'value' => $tenant['setup_score'], 'max' => 30],
                        ];
                    @endphp
                    <x-central.card class="{{ $ring['tint'] }} {{ $ring['border'] }} hover:border-honey/40 transition-colors">
                        {{-- Header: name + score ring --}}
                        <div class="mb-4 flex items-start gap-4">
                            <div class="relative h-14 w-14 flex-shrink-0">
                                <svg viewBox="0 0 48 48" class="h-14 w-14 -rotate-90">
                                    <circle cx="24" cy="24" r="20" fill="none" class="stroke-espresso" stroke-width="4" />
                                    <circle
                                        cx="24"
                                        cy="24"
                                        r="20"
                                        fill="none"
                                        class="{{ $ring['stroke'] }}"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $offset }}"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="{{ $ring['text'] }} font-bold text-[0.9rem] leading-none">{{ $score }}</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a
                                    href="{{ $this->getViewTenantUrl($tenant['id']) }}"
                                    class="hover:text-honey block truncate text-[0.95rem] font-semibold text-white no-underline"
                                >
                                    {{ $tenant['name'] }}
                                </a>
                                <div class="text-cinnamon mb-1.5 truncate text-[0.75rem]">{{ $tenant['owner'] }}</div>
                                <x-central.badge color="honey-soft" class="border-honey/25 border">
                                    {{ $tenant['plan'] }}</x-central.badge>
                            </div>
                        </div>

                        {{-- Factor bars --}}
                        <dl class="space-y-1.5">
                            @foreach ($factors as $factor)
                                @php
                                    $pct = $factor['max'] > 0 ? round(($factor['value'] / $factor['max']) * 100) : 0;
                                    $barClass = $pct >= 70 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-red-500');
                                @endphp
                                <div class="grid grid-cols-[80px_1fr_auto] items-center gap-2">
                                    <dt class="text-cinnamon text-[0.7rem] tracking-[0.06em] uppercase">
                                        {{ $factor['label'] }}
                                    </dt>
                                    <dd class="bg-espresso h-1.5 overflow-hidden rounded-full">
                                        <div
                                            class="h-full rounded-full {{ $barClass }}"
                                            style="width: {{ $pct }}%;"
                                        ></div>
                                    </dd>
                                    <dd class="text-parchment w-[44px] text-right font-mono text-[0.7rem] tabular-nums">
                                        {{ $factor['value'] }}/{{ $factor['max'] }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-central.card>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Churn Alerts Tab --}}
    @if ($activeTab === 'churn')
        @php
            $alerts = $this->getAlerts();
            $criticalCount = $alerts->where('severity', 'critical')->count();
            $warningCount = $alerts->where('severity', 'warning')->count();
        @endphp

        @if ($alerts->isEmpty())
            <x-central.card padding="py-16 px-8" class="text-center">
                <div class="mb-4">
                    <x-heroicon-o-check-circle class="inline-block h-12 w-12 text-emerald-500" />
                </div>
                <div class="text-[1.25rem] font-bold text-emerald-500">All bakeries are healthy!</div>
                <div class="text-cinnamon mt-2">No churn alerts at this time.</div>
            </x-central.card>
        @else
            {{-- Summary strip --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-central.card class="border-red-500/20 bg-red-500/5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-500/25 bg-red-500/15">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-400" />
                        </div>
                        <div>
                            <div class="text-[1.75rem] leading-none font-bold text-red-400">{{ $criticalCount }}</div>
                            <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">Critical</div>
                        </div>
                    </div>
                </x-central.card>
                <x-central.card class="border-amber-500/20 bg-amber-500/5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/15">
                            <x-heroicon-o-clock class="h-5 w-5 text-amber-400" />
                        </div>
                        <div>
                            <div class="text-[1.75rem] leading-none font-bold text-amber-400">{{ $warningCount }}</div>
                            <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">Warnings</div>
                        </div>
                    </div>
                </x-central.card>
                <x-central.card>
                    <div class="flex items-center gap-3">
                        <div class="bg-honey/15 border-honey/25 flex h-10 w-10 items-center justify-center rounded-xl border">
                            <x-heroicon-o-inbox class="text-honey h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-[1.75rem] leading-none font-bold text-white">{{ $alerts->count() }}</div>
                            <div class="text-cinnamon mt-1 text-[0.7rem] tracking-[0.08em] uppercase">Total Alerts</div>
                        </div>
                    </div>
                </x-central.card>
            </div>

            <div class="mb-3 flex items-center justify-between">
                <x-central.eyebrow>Alerts (critical first)</x-central.eyebrow>
            </div>

            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    @php
                        $isCritical = $alert['severity'] === 'critical';
                        $tone = $isCritical
                            ? ['border' => 'border-red-500/25', 'tint' => 'bg-red-500/5', 'iconBg' => 'bg-red-500/15', 'iconBorder' => 'border-red-500/25', 'iconColor' => 'text-red-400', 'pill' => 'bg-red-500/15 border-red-500/25 text-red-400', 'dot' => 'bg-red-500']
                            : ['border' => 'border-amber-500/25', 'tint' => 'bg-amber-500/5', 'iconBg' => 'bg-amber-500/15', 'iconBorder' => 'border-amber-500/25', 'iconColor' => 'text-amber-400', 'pill' => 'bg-amber-500/15 border-amber-500/25 text-amber-400', 'dot' => 'bg-amber-500'];

                        $iconComponent = match ($alert['type']) {
                            'trial_expiring' => 'heroicon-o-clock',
                            'no_login' => 'heroicon-o-no-symbol',
                            'no_orders' => 'heroicon-o-shopping-cart',
                            'low_health' => 'heroicon-o-heart',
                            default => 'heroicon-o-exclamation-triangle',
                        };
                        $extended = in_array($alert['tenant_id'], $this->extendedTrials);
                        $nudged = in_array($alert['tenant_id'], $this->sentNudges);
                    @endphp
                    <x-central.card class="{{ $tone['tint'] }} {{ $tone['border'] }} hover:border-honey/40 transition-colors">
                        <div class="flex flex-wrap items-start gap-4">
                            {{-- Icon + identity --}}
                            <div class="flex min-w-[280px] flex-1 items-start gap-3">
                                <div class="shrink-0 w-11 h-11 rounded-xl {{ $tone['iconBg'] }} {{ $tone['iconBorder'] }} border flex items-center justify-center">
                                    <x-dynamic-component
                                        :component="$iconComponent"
                                        class="w-5 h-5 {{ $tone['iconColor'] }}"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex flex-wrap items-center gap-2">
                                        <a
                                            href="{{ $this->getViewTenantUrl($alert['tenant_id']) }}"
                                            class="hover:text-honey truncate text-[0.95rem] font-semibold text-white no-underline"
                                        >
                                            {{ $alert['name'] }}
                                        </a>
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-[0.08em] {{ $tone['pill'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $tone['dot'] }}"></span>
                                            {{ $alert['type_label'] }}
                                        </span>
                                    </div>
                                    <div class="text-parchment text-[0.85rem]">{{ $alert['description'] }}</div>
                                    <div class="text-cinnamon mt-1.5 text-[0.7rem]">
                                        Signed up {{ $alert['days_since_signup'] }} days ago
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-shrink-0 items-center gap-2">
                                @if ($extended)
                                    <span class="inline-flex items-center gap-1 text-[0.75rem] text-emerald-400">
                                        <x-heroicon-o-check class="h-3.5 w-3.5" />
                                        Trial extended
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="extendTrial('{{ $alert['tenant_id'] }}')"
                                        wire:loading.attr="disabled"
                                        class="bg-espresso text-parchment border-honey/20 hover:border-honey/50 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold transition-colors"
                                    >
                                        <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                        Extend Trial
                                    </button>
                                @endif

                                @if ($nudged)
                                    <span class="inline-flex items-center gap-1 text-[0.75rem] text-emerald-400">
                                        <x-heroicon-o-check class="h-3.5 w-3.5" />
                                        Nudge sent
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="sendNudge('{{ $alert['tenant_id'] }}')"
                                        wire:loading.attr="disabled"
                                        class="bg-espresso text-parchment border-honey/20 hover:border-honey/50 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold transition-colors"
                                    >
                                        <x-heroicon-o-envelope class="h-3.5 w-3.5" />
                                        Send Nudge
                                    </button>
                                @endif

                                <a
                                    href="{{ $this->getViewTenantUrl($alert['tenant_id']) }}"
                                    class="bg-honey text-warm-black hover:bg-golden inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[0.75rem] font-semibold no-underline transition-colors"
                                >
                                    View Tenant
                                    <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                                </a>
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
                    <x-heroicon-o-check-circle class="inline-block h-12 w-12 text-emerald-500" />
                </div>
                <div class="mb-2 text-base font-bold text-emerald-500">All Tenants Within Limits</div>
                <p class="text-cinnamon mx-auto max-w-[480px]">
                    No bakeries are currently approaching their plan limits. When tenants reach 80% or more of their
                    product or order limits, they'll appear here as upgrade candidates.
                </p>
            </x-central.card>
        @else
            <div class="mb-4">
                <p class="text-cinnamon text-sm">
                    <span class="text-honey font-bold">{{ $tenants->count() }}</span>
                    tenant{{ $tenants->count() !== 1 ? 's' : '' }} approaching or at plan limits
                </p>
            </div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(380px,1fr))] gap-4">
                @foreach ($tenants as $t)
                    <x-central.card
                        :class="$t['at_limit'] ? 'border-red-500' : 'border-amber-500/30'"
                        class="transition-transform"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <div class="mb-0.5 text-base font-bold text-white">{{ $t['name'] }}</div>
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
                            <div class="mb-1 flex justify-between">
                                <x-central.eyebrow as="span">Products</x-central.eyebrow>
                                <span class="text-parchment text-xs font-semibold">{{ $t['product_count'] }} / {{ $t['product_limit'] }}</span>
                            </div>
                            <div class="bg-espresso h-2 overflow-hidden rounded">
                                <div
                                    class="h-full rounded transition-all duration-300 {{ $pBarClass }}"
                                    style="width: {{ min($t['product_percent'], 100) }}%;"
                                ></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="mb-1 flex justify-between">
                                <x-central.eyebrow as="span">Orders This Month</x-central.eyebrow>
                                <span class="text-parchment text-xs font-semibold">{{ $t['order_count'] }} / {{ $t['order_limit'] }}</span>
                            </div>
                            <div class="bg-espresso h-2 overflow-hidden rounded">
                                <div
                                    class="h-full rounded transition-all duration-300 {{ $oBarClass }}"
                                    style="width: {{ min($t['order_percent'], 100) }}%;"
                                ></div>
                            </div>
                        </div>

                        <div class="border-honey/8 border-t pt-4">
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
