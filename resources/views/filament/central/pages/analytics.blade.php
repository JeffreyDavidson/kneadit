<x-filament-panels::page>
    {{-- KPI Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ($this->getKpis() as $kpi)
            @php
                $trendClass = match ($kpi['trend']) {
                    'up' => 'text-emerald-400',
                    'down' => 'text-red-400',
                    'neutral' => 'text-cinnamon',
                    default => 'text-cinnamon',
                };
            @endphp
            <x-central.card padding="p-5">
                <x-central.eyebrow>{{ $kpi['label'] }}</x-central.eyebrow>
                <div class="text-white font-bold text-[2rem] leading-none mt-2 mb-2">{{ $kpi['value'] }}</div>
                <div class="text-[0.75rem] font-medium {{ $trendClass }}">{{ $kpi['hint'] }}</div>
            </x-central.card>
        @endforeach
    </div>

    {{-- Signups Chart + Tenant Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6 mb-6">
        <x-central.card title="Signups Over Last 12 Months">
            <div class="relative h-[280px]">
                <canvas id="signupsChart"></canvas>
            </div>
        </x-central.card>
        <x-central.card title="Tenant Lifecycle">
            <div class="relative h-[200px] mb-4">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="space-y-2 mt-4">
                @foreach ($this->getTenantStatus() as $label => $count)
                    @php
                        $color = match ($label) {
                            'Active' => 'bg-emerald-500',
                            'On trial' => 'bg-honey',
                            'Trial expired' => 'bg-red-500',
                            default => 'bg-cinnamon',
                        };
                    @endphp
                    <div class="flex items-center justify-between text-[0.8rem]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $color }}"></span>
                            <span class="text-parchment">{{ $label }}</span>
                        </div>
                        <span class="text-white font-bold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-central.card>
    </div>

    {{-- Plan Distribution --}}
    <x-central.card title="Plan Distribution">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.5fr] gap-8 items-center">
            <div class="relative h-[240px]">
                <canvas id="planChart"></canvas>
            </div>
            <div class="space-y-3">
                @php
                    $plans = $this->getPlanDistribution();
                    $total = array_sum($plans) ?: 1;
                    $planColors = [
                        'free' => 'bg-cinnamon',
                        'starter' => 'bg-sky-400',
                        'growth' => 'bg-emerald-400',
                        'pro' => 'bg-honey',
                    ];
                @endphp
                @foreach ($plans as $plan => $count)
                    @php
                        $pct = round($count / $total * 100, 1);
                        $color = $planColors[strtolower((string) $plan)] ?? 'bg-cinnamon';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $color }}"></span>
                                <span class="text-white text-[0.85rem] font-semibold capitalize">{{ $plan }}</span>
                            </div>
                            <div class="text-cinnamon text-[0.8rem]">
                                <span class="text-white font-bold">{{ $count }}</span> <span class="text-cinnamon">({{ $pct }}%)</span>
                            </div>
                        </div>
                        <div class="h-1.5 rounded-full bg-espresso overflow-hidden">
                            <div class="{{ $color }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-central.card>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script @cspnonce>
        function initAnalyticsCharts() {
            const honey = '#d4920c';
            const golden = '#e8b04a';
            const emerald = '#10b981';
            const sky = '#38bdf8';
            const red = '#ef4444';
            const cinnamon = '#8b6844';

            Chart.defaults.color = '#faf0d6';
            Chart.defaults.borderColor = 'rgba(212,146,12,0.08)';
            Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

            // Chart.js v4 dropped Chart.helpers.each; iterate the instances map directly.
            Object.values(Chart.instances).forEach((instance) => instance.destroy());

            const signupsEl = document.getElementById('signupsChart');
            const planEl = document.getElementById('planChart');
            const statusEl = document.getElementById('statusChart');

            if (!signupsEl || !planEl || !statusEl) return;

            const signups = @json($this->getSignupsByMonth());
            const currentMonthIdx = signups.length - 1;
            new Chart(signupsEl, {
                type: 'bar',
                data: {
                    labels: signups.map(s => s.label),
                    datasets: [{
                        label: 'Signups',
                        data: signups.map(s => s.count),
                        backgroundColor: signups.map((_, i) => i === currentMonthIdx ? honey : 'rgba(212,146,12,0.35)'),
                        hoverBackgroundColor: honey,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1c1410',
                            borderColor: 'rgba(212,146,12,0.25)',
                            borderWidth: 1,
                            padding: 10,
                            titleColor: '#faf0d6',
                            bodyColor: '#ffffff',
                            displayColors: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#8b6844' },
                            grid: { color: 'rgba(212,146,12,0.06)' },
                        },
                        x: {
                            ticks: { color: '#8b6844' },
                            grid: { display: false },
                        }
                    }
                }
            });

            const plans = @json($this->getPlanDistribution());
            const planColorMap = { free: cinnamon, starter: sky, growth: emerald, pro: honey };
            const planLabels = Object.keys(plans);
            new Chart(planEl, {
                type: 'doughnut',
                data: {
                    labels: planLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data: Object.values(plans),
                        backgroundColor: planLabels.map(l => planColorMap[l.toLowerCase()] ?? cinnamon),
                        borderColor: '#1c1410',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1c1410',
                            borderColor: 'rgba(212,146,12,0.25)',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

            const status = @json($this->getTenantStatus());
            const statusLabels = Object.keys(status);
            const statusColorMap = { 'Active': emerald, 'On trial': honey, 'Trial expired': red };
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: Object.values(status),
                        backgroundColor: statusLabels.map(l => statusColorMap[l] ?? cinnamon),
                        borderColor: '#1c1410',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1c1410',
                            borderColor: 'rgba(212,146,12,0.25)',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                        }
                    }
                }
            });
        }

        function loadChartJs() {
            if (typeof Chart !== 'undefined') return Promise.resolve();
            if (window.__chartJsPromise) return window.__chartJsPromise;
            window.__chartJsPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('Chart.js failed to load'));
                document.head.appendChild(script);
            });
            return window.__chartJsPromise;
        }

        function tryInitCharts() {
            const el = document.getElementById('signupsChart');
            if (!el) return;
            loadChartJs().then(() => {
                initAnalyticsCharts();
                // After SPA navigation Filament's grid hasn't finished reflowing
                // when Chart.js samples the canvas parent, so the chart keeps a
                // stale width and overflows the viewport. Watch each chart's
                // parent and resize whenever the actual width changes.
                if (typeof ResizeObserver !== 'undefined') {
                    Object.values(Chart.instances).forEach((instance) => {
                        const parent = instance.canvas.parentElement;
                        if (!parent) return;
                        // Guard the callback: Livewire SPA navigation can detach
                        // the canvas before the observer fires, and Chart.js
                        // resize() then dereferences a null parentElement.
                        new ResizeObserver(() => {
                            if (instance.canvas?.parentElement) {
                                instance.resize();
                            }
                        }).observe(parent);
                    });
                }
            }).catch((err) => console.error(err));
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            requestAnimationFrame(tryInitCharts);
        } else {
            document.addEventListener('DOMContentLoaded', tryInitCharts);
        }
        document.addEventListener('livewire:navigated', () => requestAnimationFrame(tryInitCharts));
    </script>
</x-filament-panels::page>
