<x-filament-panels::page>
    @php
        $leaderboard = $this->getLeaderboardData();
        $summary = $this->getSummaryStats();
        $top3 = array_slice($leaderboard, 0, 3);
        $rankColors = ['#d4920c', '#94a3b8', '#b45309'];
        $rankLabels = ['🥇 Gold', '🥈 Silver', '🥉 Bronze'];
        $podiumHeights = [160, 120, 100];
    @endphp

    {{-- Summary Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="color: #e8b04a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Total Platform Orders</div>
            <div style="color: #faf0d6; font-size: 32px; font-weight: 800; margin-top: 4px;">{{ number_format($summary['total_orders']) }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="color: #e8b04a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Average Orders / Bakery</div>
            <div style="color: #faf0d6; font-size: 32px; font-weight: 800; margin-top: 4px;">{{ $summary['avg_orders'] }}</div>
        </div>
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="color: #e8b04a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Total Bakeries</div>
            <div style="color: #faf0d6; font-size: 32px; font-weight: 800; margin-top: 4px;">{{ $summary['total_bakeries'] }}</div>
        </div>
    </div>

    {{-- Podium --}}
    @if (count($top3) >= 3)
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 32px; margin-bottom: 24px;">
            <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600; text-align: center; margin-bottom: 24px;">🏆 Top 3 Bakeries</h3>
            <div style="display: flex; align-items: flex-end; justify-content: center; gap: 16px; padding-top: 20px;">
                {{-- 2nd place --}}
                <div style="text-align: center; width: 160px;">
                    <div style="color: #faf0d6; font-size: 14px; font-weight: 600; margin-bottom: 8px;">{{ $top3[1]['name'] }}</div>
                    <div style="color: #94a3b8; font-size: 12px; margin-bottom: 8px;">{{ $top3[1]['total_orders'] }} orders</div>
                    <div style="background: #94a3b8; height: {{ $podiumHeights[1] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #0c0a09; font-size: 28px; font-weight: 800;">#2</span>
                    </div>
                </div>
                {{-- 1st place --}}
                <div style="text-align: center; width: 180px;">
                    <div style="color: #faf0d6; font-size: 16px; font-weight: 700; margin-bottom: 8px;">{{ $top3[0]['name'] }}</div>
                    <div style="color: #d4920c; font-size: 12px; margin-bottom: 8px;">{{ $top3[0]['total_orders'] }} orders</div>
                    <div style="background: #d4920c; height: {{ $podiumHeights[0] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #0c0a09; font-size: 32px; font-weight: 800;">#1</span>
                    </div>
                </div>
                {{-- 3rd place --}}
                <div style="text-align: center; width: 160px;">
                    <div style="color: #faf0d6; font-size: 14px; font-weight: 600; margin-bottom: 8px;">{{ $top3[2]['name'] }}</div>
                    <div style="color: #b45309; font-size: 12px; margin-bottom: 8px;">{{ $top3[2]['total_orders'] }} orders</div>
                    <div style="background: #b45309; height: {{ $podiumHeights[2] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #0c0a09; font-size: 28px; font-weight: 800;">#3</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Leaderboard Table --}}
    <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #3d2c1e;">
            <h3 style="color: #e8b04a; font-size: 16px; font-weight: 600;">Full Rankings</h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #3d2c1e;">
                        <th style="padding: 12px 16px; text-align: left; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Rank</th>
                        <th style="padding: 12px 16px; text-align: left; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Bakery</th>
                        <th style="padding: 12px 16px; text-align: left; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Owner</th>
                        <th style="padding: 12px 16px; text-align: center; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Plan</th>
                        <th style="padding: 12px 16px; text-align: right; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Total Orders</th>
                        <th style="padding: 12px 16px; text-align: right; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">This Month</th>
                        <th style="padding: 12px 16px; text-align: right; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Products</th>
                        <th style="padding: 12px 16px; text-align: right; color: #e8b04a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Avg Review</th>
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
                            $rowBg = $isTop3 ? 'rgba(212, 146, 12, 0.05)' : 'transparent';
                        @endphp
                        <tr style="border-bottom: 1px solid #2a1f18; background: {{ $rowBg }};">
                            <td style="padding: 12px 16px;">
                                <span style="color: {{ $rankColor }}; font-weight: 800; font-size: {{ $isTop3 ? '18px' : '14px' }};">#{{ $rank }}</span>
                            </td>
                            <td style="padding: 12px 16px; color: #faf0d6; font-weight: 600;">{{ $tenant['name'] }}</td>
                            <td style="padding: 12px 16px; color: #f5d88e; font-size: 13px;">{{ $tenant['owner'] }}</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span style="background: {{ $tenant['plan'] === 'premium' ? '#d4920c' : ($tenant['plan'] === 'pro' ? '#e8b04a' : '#3d2c1e') }}; color: {{ $tenant['plan'] === 'free' ? '#f5d88e' : '#0c0a09' }}; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                    {{ $tenant['plan'] }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; color: #faf0d6; font-weight: 700;">{{ $tenant['total_orders'] }}</td>
                            <td style="padding: 12px 16px; text-align: right; color: #f5d88e;">{{ $tenant['month_orders'] }}</td>
                            <td style="padding: 12px 16px; text-align: right; color: #f5d88e;">{{ $tenant['total_products'] }}</td>
                            <td style="padding: 12px 16px; text-align: right; color: #f5d88e;">{{ $tenant['avg_review'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
