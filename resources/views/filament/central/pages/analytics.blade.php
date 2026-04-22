<x-filament-panels::page>
    {{-- Summary Stats --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-central.stat-card label="Total Signups" value-class="text-[1.75rem] text-white">{{ $this->getTotalSignups() }}</x-central.stat-card>
        <x-central.stat-card label="This Month" value-class="text-[1.75rem] text-white">{{ $this->getThisMonthSignups() }}</x-central.stat-card>
        <x-central.stat-card label="Avg Days on Trial" value-class="text-[1.75rem] text-white">{{ $this->getAvgDaysOnTrial() }}</x-central.stat-card>
        <x-central.stat-card label="Most Popular Plan" value-class="text-[1.25rem] text-white capitalize">{{ $this->getMostPopularPlan() }}</x-central.stat-card>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-2 gap-6 mb-6">
        <x-central.card title="Signups Over Last 12 Months">
            <canvas id="signupsChart" height="250"></canvas>
        </x-central.card>
        <x-central.card title="Plan Distribution" class="overflow-hidden">
            <div class="relative max-h-[280px]">
                <canvas id="planChart"></canvas>
            </div>
        </x-central.card>
    </div>
    <x-central.card title="Monthly Growth Rate (%)">
        <canvas id="growthChart" height="150"></canvas>
    </x-central.card>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function initAnalyticsCharts() {
            const gold = '#d4920c';
            const lightGold = '#e8b04a';
            const brown = '#8b6844';
            const darkBrown = '#5c4333';
            const paleGold = '#f5d88e';
            Chart.defaults.color = '#faf0d6';
            Chart.defaults.borderColor = 'rgba(212,146,12,0.12)';

            // Destroy existing charts to prevent duplicates
            Chart.helpers.each(Chart.instances, (instance) => instance.destroy());

            const signupsEl = document.getElementById('signupsChart');
            const planEl = document.getElementById('planChart');
            const growthEl = document.getElementById('growthChart');

            if (!signupsEl || !planEl || !growthEl) return;

            const signups = @json($this->getSignupsByMonth());
            new Chart(signupsEl, {
                type: 'bar',
                data: {
                    labels: signups.map(s => s.label),
                    datasets: [{
                        label: 'Signups',
                        data: signups.map(s => s.count),
                        backgroundColor: gold,
                        borderRadius: 4,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            const plans = @json($this->getPlanDistribution());
            const planLabels = Object.keys(plans);
            const planColors = [gold, lightGold, brown, darkBrown, paleGold];
            new Chart(planEl, {
                type: 'doughnut',
                data: {
                    labels: planLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data: Object.values(plans),
                        backgroundColor: planColors.slice(0, planLabels.length),
                        borderWidth: 0,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
            });

            const growth = @json($this->getMonthlyGrowth());
            new Chart(growthEl, {
                type: 'line',
                data: {
                    labels: growth.map(g => g.label),
                    datasets: [{
                        label: 'Growth %',
                        data: growth.map(g => g.rate),
                        borderColor: gold,
                        backgroundColor: 'rgba(212,146,12,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: lightGold,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => v + '%' } } } }
            });
        }

        function tryInitCharts(attempts = 0) {
            const el = document.getElementById('signupsChart');
            if (el) {
                initAnalyticsCharts();
            } else if (attempts < 10) {
                setTimeout(() => tryInitCharts(attempts + 1), 100);
            }
        }

        // Init on first load and SPA navigation
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            tryInitCharts();
        } else {
            document.addEventListener('DOMContentLoaded', tryInitCharts);
        }
        document.addEventListener('livewire:navigated', () => setTimeout(tryInitCharts, 50));
    </script>
</x-filament-panels::page>
