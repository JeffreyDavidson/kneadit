<x-filament-panels::page>
    {{-- KPI Row --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($this->getKpis() as $kpi)
            @php
                $trendClass = match ($kpi['trend']) {
                    'up' => 'text-(--kn-success)',
                    'down' => 'text-(--kn-danger)',
                    'neutral' => 'text-(--kn-muted)',
                    default => 'text-(--kn-muted)',
                };
            @endphp
            <x-central.card padding="p-5">
                <x-central.eyebrow>{{ $kpi['label'] }}</x-central.eyebrow>
                <div class="mt-2 mb-2 text-[2rem] leading-none font-bold text-(--kn-ink)">{{ $kpi['value'] }}</div>
                <div class="text-[0.75rem] font-medium {{ $trendClass }}">{{ $kpi['hint'] }}</div>
            </x-central.card>
        @endforeach
    </div>

    {{-- Signups Chart + Tenant Status --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-[2fr_1fr]">
        <x-central.card title="Signups Over Last 12 Months">
            <div class="relative h-[280px]">
                <canvas id="signupsChart"></canvas>
            </div>
        </x-central.card>
        <x-central.card title="Tenant Lifecycle">
            <div class="relative mb-4 h-[200px]">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 space-y-2">
                @foreach ($this->getTenantStatus() as $label => $count)
                    @php
                        $color = match ($label) {
                            'Active' => 'bg-(--kn-success)',
                            'On trial' => 'bg-(--kn-honey)',
                            'Trial expired' => 'bg-(--kn-danger)',
                            default => 'bg-(--kn-muted)',
                        };
                    @endphp
                    <div class="flex items-center justify-between text-[0.8rem]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $color }}"></span>
                            <span class="text-(--kn-ink)">{{ $label }}</span>
                        </div>
                        <span class="font-bold text-(--kn-ink)">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-central.card>
    </div>

    {{-- Plan Distribution --}}
    <x-central.card title="Plan Distribution">
        <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-[1fr_1.5fr]">
            <div class="relative h-[240px]">
                <canvas id="planChart"></canvas>
            </div>
            <div class="space-y-3">
                @php
                    $plans = $this->getPlanDistribution();
                    $total = array_sum($plans) ?: 1;
                    $planColors = [
                        'free' => 'bg-(--kn-muted)',
                        'starter' => 'bg-(--kn-info)',
                        'growth' => 'bg-(--kn-success)',
                        'pro' => 'bg-(--kn-honey)',
                    ];
                @endphp
                @foreach ($plans as $plan => $count)
                    @php
                        $pct = round($count / $total * 100, 1);
                        $color = $planColors[strtolower((string) $plan)] ?? 'bg-(--kn-muted)';
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $color }}"></span>
                                <span class="text-[0.85rem] font-semibold text-(--kn-ink) capitalize">{{ $plan }}</span>
                            </div>
                            <div class="text-[0.8rem] text-(--kn-muted)">
                                <span class="font-bold text-(--kn-ink)">{{ $count }}</span>
                                <span class="text-(--kn-muted)">({{ $pct }}%)</span>
                            </div>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-(--kn-surface-sunken)">
                            <div class="{{ $color }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-central.card>

    <script @cspnonce>
        function initAnalyticsCharts() {
            // Chart.js draws on a canvas and can't read CSS variables, so the design
            // system tokens are read once here, for the theme that is showing.
            const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            const withAlpha = (hex, alpha) =>
                hex +
                Math.round(alpha * 255)
                    .toString(16)
                    .padStart(2, '0');
            const honey = token('--kn-honey');
            const emerald = token('--kn-success');
            const sky = token('--kn-info');
            const red = token('--kn-danger');
            const cinnamon = token('--kn-muted');
            const surface = token('--kn-surface');
            const grid = token('--kn-border');
            const tooltip = {
                backgroundColor: token('--kn-espresso'),
                borderColor: token('--kn-border-control'),
                borderWidth: 1,
                padding: 10,
                titleColor: token('--kn-on-espresso'),
                bodyColor: token('--kn-on-espresso'),
            };

            Chart.defaults.color = token('--kn-muted');
            Chart.defaults.borderColor = grid;
            Chart.defaults.font.family = '"Instrument Sans", ui-sans-serif, system-ui, sans-serif';

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
                    labels: signups.map((s) => s.label),
                    datasets: [
                        {
                            label: 'Signups',
                            data: signups.map((s) => s.count),
                            backgroundColor: signups.map((_, i) => (i === currentMonthIdx ? honey : withAlpha(honey, 0.35))),
                            hoverBackgroundColor: honey,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltip, displayColors: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: cinnamon },
                            grid: { color: grid },
                        },
                        x: {
                            ticks: { color: cinnamon },
                            grid: { display: false },
                        },
                    },
                },
            });

            const plans = @json($this->getPlanDistribution());
            const planColorMap = { free: cinnamon, starter: sky, growth: emerald, pro: honey };
            const planLabels = Object.keys(plans);
            new Chart(planEl, {
                type: 'doughnut',
                data: {
                    labels: planLabels.map((l) => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [
                        {
                            data: Object.values(plans),
                            backgroundColor: planLabels.map((l) => planColorMap[l.toLowerCase()] ?? cinnamon),
                            borderColor: surface,
                            borderWidth: 3,
                            hoverOffset: 8,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...tooltip,
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });

            const status = @json($this->getTenantStatus());
            const statusLabels = Object.keys(status);
            const statusColorMap = { Active: emerald, 'On trial': honey, 'Trial expired': red };
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [
                        {
                            data: Object.values(status),
                            backgroundColor: statusLabels.map((l) => statusColorMap[l] ?? cinnamon),
                            borderColor: surface,
                            borderWidth: 3,
                            hoverOffset: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltip, displayColors: false },
                    },
                },
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
            loadChartJs()
                .then(() => {
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
                })
                .catch((err) => console.error(err));
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            requestAnimationFrame(tryInitCharts);
        } else {
            document.addEventListener('DOMContentLoaded', tryInitCharts);
        }
        document.addEventListener('livewire:navigated', () => requestAnimationFrame(tryInitCharts));

        // Redraw when the Light / Dark switch changes, so the chart colours follow it.
        if (!window.__analyticsThemeListener) {
            window.__analyticsThemeListener = true;
            window.addEventListener('theme-changed', () => requestAnimationFrame(tryInitCharts));
        }
    </script>
</x-filament-panels::page>
