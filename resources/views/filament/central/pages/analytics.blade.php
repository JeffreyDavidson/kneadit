<x-filament-panels::page>
    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Total Signups</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ffffff;">{{ $this->getTotalSignups() }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">This Month</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ffffff;">{{ $this->getThisMonthSignups() }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Avg Days on Trial</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #ffffff;">{{ $this->getAvgDaysOnTrial() }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Most Popular Plan</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #ffffff; text-transform: capitalize;">{{ $this->getMostPopularPlan() }}</div>
        </div>
    </div>

    {{-- Charts --}}
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #ffffff; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Signups Over Last 12 Months</div>
            <canvas id="signupsChart" height="250"></canvas>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: #ffffff; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Plan Distribution</div>
            <canvas id="planChart" height="250"></canvas>
        </div>
    </div>
    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
        <div style="color: #ffffff; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Monthly Growth Rate (%)</div>
        <canvas id="growthChart" height="150"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gold = '#d4920c';
            const lightGold = '#e8b04a';
            const brown = '#8b6844';
            const darkBrown = '#5c4333';
            const paleGold = '#f5d88e';
            Chart.defaults.color = '#faf0d6';
            Chart.defaults.borderColor = 'rgba(212,146,12,0.12)';

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
