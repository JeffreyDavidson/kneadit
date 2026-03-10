<x-filament-panels::page>
    <style>
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .analytics-card { background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 0.75rem; padding: 1.5rem; }
        .analytics-card-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #f5d88e; margin-bottom: 0.25rem; }
        .analytics-card-value { font-size: 1.75rem; font-weight: 700; color: #ffffff; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
        .chart-container { background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 0.75rem; padding: 1.5rem; }
        .chart-title { font-size: 1rem; font-weight: 600; color: #ffffff; margin-bottom: 1rem; }
    </style>

    <div class="analytics-grid">
        <div class="analytics-card">
            <div class="analytics-card-label">Total Signups</div>
            <div class="analytics-card-value">{{ $this->getTotalSignups() }}</div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card-label">This Month</div>
            <div class="analytics-card-value">{{ $this->getThisMonthSignups() }}</div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card-label">Avg Days on Trial</div>
            <div class="analytics-card-value">{{ $this->getAvgDaysOnTrial() }}</div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card-label">Most Popular Plan</div>
            <div class="analytics-card-value" style="font-size: 1.25rem; text-transform: capitalize;">{{ $this->getMostPopularPlan() }}</div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-container">
            <div class="chart-title">Signups Over Last 12 Months</div>
            <canvas id="signupsChart" height="250"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Plan Distribution</div>
            <canvas id="planChart" height="250"></canvas>
        </div>
        <div class="chart-container" style="grid-column: 1 / -1;">
            <div class="chart-title">Monthly Growth Rate (%)</div>
            <canvas id="growthChart" height="150"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gold = '#d4920c';
            const lightGold = '#e8b04a';
            const brown = '#8b6844';
            const darkBrown = '#5c4333';
            const paleGold = '#f5d88e';
            const chartDefaults = { color: '#faf0d6', borderColor: 'rgba(212,146,12,0.12)' };
            Chart.defaults.color = chartDefaults.color;
            Chart.defaults.borderColor = chartDefaults.borderColor;

            const signups = @json($this->getSignupsByMonth());
            new Chart(document.getElementById('signupsChart'), {
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
            new Chart(document.getElementById('planChart'), {
                type: 'doughnut',
                data: {
                    labels: planLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data: Object.values(plans),
                        backgroundColor: planColors.slice(0, planLabels.length),
                        borderWidth: 0,
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });

            const growth = @json($this->getMonthlyGrowth());
            new Chart(document.getElementById('growthChart'), {
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
        });
    </script>
</x-filament-panels::page>
