<x-filament-panels::page>
    @php
        $leaderboard = $this->getLeaderboardData();
        $summary = $this->getSummaryStats();
        $top3 = array_slice($leaderboard, 0, 3);
        $rankColors = ['#d4920c', '#94a3b8', '#b45309'];
        $rankLabels = ['🥇 Gold', '🥈 Silver', '🥉 Bronze'];
        $podiumHeights = [140, 110, 90];
    @endphp

    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Total Platform Orders</div>
            <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ number_format($summary['total_orders']) }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Average Orders / Bakery</div>
            <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ $summary['avg_orders'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; text-align: center;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">Total Bakeries</div>
            <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ $summary['total_bakeries'] }}</div>
        </div>
    </div>

    {{-- Podium --}}
    @if (count($top3) >= 3)
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; text-align: center; margin-bottom: 1.5rem;">
                <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 0.25rem;" viewBox="0 0 24 24" fill="#d4920c" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Top 3 Bakeries
            </div>
            <div style="display: flex; align-items: flex-end; justify-content: center; gap: 1rem; padding-top: 1rem;">
                {{-- 2nd place --}}
                <div style="text-align: center; width: 160px;">
                    <div style="color: #faf0d6; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $top3[1]['name'] }}</div>
                    <div style="color: #8b6844; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[1]['total_orders'] }} orders</div>
                    <div style="background: linear-gradient(180deg, #94a3b8, #64748b); height: {{ $podiumHeights[1] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 1.75rem; font-weight: 700;">#2</span>
                    </div>
                </div>
                {{-- 1st place --}}
                <div style="text-align: center; width: 180px;">
                    <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $top3[0]['name'] }}</div>
                    <div style="color: #d4920c; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[0]['total_orders'] }} orders</div>
                    <div style="background: linear-gradient(180deg, #e8b04a, #d4920c); height: {{ $podiumHeights[0] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 1.75rem; font-weight: 700;">#1</span>
                    </div>
                </div>
                {{-- 3rd place --}}
                <div style="text-align: center; width: 160px;">
                    <div style="color: #faf0d6; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $top3[2]['name'] }}</div>
                    <div style="color: #8b6844; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[2]['total_orders'] }} orders</div>
                    <div style="background: linear-gradient(180deg, #b45309, #92400e); height: {{ $podiumHeights[2] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 1.75rem; font-weight: 700;">#3</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Leaderboard Table --}}
    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(212,146,12,0.08);">
            <div style="color: white; font-weight: 700; font-size: 1rem;">Full Rankings</div>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(212,146,12,0.08);">
                        <th style="padding: 0.75rem 1rem; text-align: left; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Rank</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Bakery</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Owner</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Plan</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Total Orders</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">This Month</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Products</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Avg Review</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaderboard as $idx => $tenant)
                        @php
                            $rank = $idx + 1;
                            $isTop3 = $rank <= 3;
                            $rankColor = match($rank) {
                                1 => '#d4920c',
                                2 => '#94a3b8',
                                3 => '#b45309',
                                default => '#faf0d6',
                            };
                            $rowBg = $isTop3 ? 'rgba(212,146,12,0.05)' : 'transparent';
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(212,146,12,0.08); background: {{ $rowBg }};">
                            <td style="padding: 0.75rem 1rem;">
                                <span style="color: {{ $rankColor }}; font-weight: 700; font-size: {{ $isTop3 ? '1.1rem' : '0.875rem' }};">#{{ $rank }}</span>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: white; font-weight: 700;">{{ $tenant['name'] }}</td>
                            <td style="padding: 0.75rem 1rem; color: #faf0d6; font-size: 0.8rem;">{{ $tenant['owner'] }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <span style="display: inline-block; background: {{ $tenant['plan'] === 'premium' ? '#d4920c' : ($tenant['plan'] === 'pro' ? '#e8b04a' : 'rgba(212,146,12,0.1)') }}; color: {{ $tenant['plan'] === 'free' ? '#d4920c' : '#1c1410' }}; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">
                                    {{ $tenant['plan'] }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: white; font-weight: 700;">{{ $tenant['total_orders'] }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #faf0d6;">{{ $tenant['month_orders'] }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #faf0d6;">{{ $tenant['total_products'] }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #faf0d6;">{{ $tenant['avg_review'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
