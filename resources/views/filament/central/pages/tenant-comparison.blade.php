<x-filament-panels::page>
    {{-- Tab Switcher --}}
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        @foreach ([
            'compare' => 'Compare',
            'leaderboard' => 'Leaderboard',
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

    {{-- Compare Tab --}}
    @if ($activeTab === 'compare')
        @php
            $allTenants = $this->getAllTenants();
            $comparisonData = $this->getComparisonData();
        @endphp

        <div x-data="{
            tenant1: '{{ $this->selectedTenants[0] ?? '' }}',
            tenant2: '{{ $this->selectedTenants[1] ?? '' }}',
            tenant3: '{{ $this->selectedTenants[2] ?? '' }}',
            compare() {
                const params = new URLSearchParams();
                if (this.tenant1) params.append('tenants[]', this.tenant1);
                if (this.tenant2) params.append('tenants[]', this.tenant2);
                if (this.tenant3) params.append('tenants[]', this.tenant3);
                window.location.search = params.toString();
            }
        }" style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Select Tenants to Compare</div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                @for ($i = 1; $i <= 3; $i++)
                    <div style="flex: 1; min-width: 200px;">
                        <label style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; display: block; margin-bottom: 0.25rem;">Tenant {{ $i }}</label>
                        <select x-model="tenant{{ $i }}" style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); color: #faf0d6; padding: 0.5rem 2rem 0.5rem 0.75rem; border-radius: 8px; font-size: 0.875rem; -webkit-appearance: none; appearance: none; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23d4920c%22 stroke-width=%222%22><path d=%22M6 9l6 6 6-6%22/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center;">
                            <option value="">— Select —</option>
                            @foreach ($allTenants as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endfor
                <button @click="compare()" style="background: #d4920c; color: #1c1410; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; height: 38px;">
                    Compare
                </button>
            </div>
        </div>

        @if (count($comparisonData) > 0)
            <div style="display: grid; grid-template-columns: repeat({{ count($comparisonData) }}, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                @foreach ($comparisonData as $tenant)
                    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column;">
                        <div style="margin-bottom: 1rem; text-align: center; padding-bottom: 1rem; border-bottom: 1px solid rgba(212,146,12,0.08);">
                            <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $tenant['name'] }}</div>
                            <span style="display: inline-block; background: {{ $tenant['plan'] === 'premium' ? '#d4920c' : ($tenant['plan'] === 'pro' ? '#e8b04a' : 'rgba(212,146,12,0.1)') }}; color: {{ $tenant['plan'] === 'free' ? '#d4920c' : '#1c1410' }}; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">
                                {{ $tenant['plan'] }}
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
                            @php
                                $rows = [
                                    ['label' => 'Total Orders', 'value' => $tenant['total_orders']],
                                    ['label' => 'This Month', 'value' => $tenant['month_orders']],
                                    ['label' => 'Products', 'value' => $tenant['total_products']],
                                    ['label' => 'Categories', 'value' => $tenant['total_categories']],
                                    ['label' => 'Avg Review', 'value' => ($tenant['avg_review'] ?: '—') . '/5'],
                                    ['label' => 'Setup', 'value' => $tenant['setup_completed'] . '/7'],
                                    ['label' => 'Days Since Signup', 'value' => $tenant['days_since_signup']],
                                ];
                            @endphp
                            @foreach ($rows as $row)
                                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: #2a1f18; border-radius: 8px;">
                                    <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; align-self: center;">{{ $row['label'] }}</span>
                                    <span style="color: #faf0d6; font-weight: 700;">{{ $row['value'] }}</span>
                                </div>
                            @endforeach
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: #2a1f18; border-radius: 8px;">
                                <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; align-self: center;">Health Score</span>
                                <span style="color: {{ $tenant['health_score'] > 70 ? '#10b981' : ($tenant['health_score'] >= 40 ? '#f59e0b' : '#ef4444') }}; font-weight: 700;">{{ $tenant['health_score'] }}/100</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Visual Comparison</div>
                @php
                    $chartMetrics = ['total_orders', 'total_products', 'health_score'];
                    $chartLabels = ['Total Orders', 'Total Products', 'Health Score'];
                    $barColors = ['#d4920c', '#e8b04a', '#f5d88e'];
                @endphp
                @foreach ($chartMetrics as $idx => $metric)
                    @php $maxVal = max(array_column($comparisonData, $metric)) ?: 1; @endphp
                    <div style="margin-bottom: 1.5rem;">
                        <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">{{ $chartLabels[$idx] }}</div>
                        @foreach ($comparisonData as $tIdx => $tenant)
                            @php $pct = round(($tenant[$metric] / $maxVal) * 100); @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                                <span style="color: #faf0d6; font-size: 0.75rem; width: 120px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tenant['name'] }}</span>
                                <div style="flex: 1; background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColors[$tIdx % 3] }}; border-radius: 4px; transition: width 0.3s;"></div>
                                </div>
                                <span style="color: #faf0d6; font-size: 0.8rem; font-weight: 700; width: 50px;">{{ $tenant[$metric] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 3rem; text-align: center;">
                <div style="margin-bottom: 1rem;">
                    <svg style="width: 48px; height: 48px; display: inline-block;" viewBox="0 0 24 24" fill="none" stroke="#d4920c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div style="color: #faf0d6; font-size: 1rem;">Select 2–3 tenants above to compare their metrics side by side.</div>
            </div>
        @endif
    @endif

    {{-- Leaderboard Tab --}}
    @if ($activeTab === 'leaderboard')
        @php
            $leaderboard = $this->getLeaderboardData();
            $summary = $this->getLeaderboardSummaryStats();
            $top3 = array_slice($leaderboard, 0, 3);
            $podiumHeights = [140, 110, 90];
        @endphp

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Total Platform Orders</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ number_format($summary['total_orders']) }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Average Orders / Bakery</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ $summary['avg_orders'] }}</div>
            </div>
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.25rem;">Total Bakeries</div>
                <div style="color: white; font-size: 1.75rem; font-weight: 700;">{{ $summary['total_bakeries'] }}</div>
            </div>
        </div>

        @if (count($top3) >= 3)
            <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                <div style="color: white; font-weight: 700; font-size: 1rem; text-align: center; margin-bottom: 1.5rem;">
                    <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 0.25rem;" viewBox="0 0 24 24" fill="#d4920c" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Top 3 Bakeries
                </div>
                <div style="display: flex; align-items: flex-end; justify-content: center; gap: 1rem; padding-top: 1rem;">
                    <div style="text-align: center; width: 160px;">
                        <div style="color: #faf0d6; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $top3[1]['name'] }}</div>
                        <div style="color: #8b6844; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[1]['total_orders'] }} orders</div>
                        <div style="background: linear-gradient(180deg, #94a3b8, #64748b); height: {{ $podiumHeights[1] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 1.75rem; font-weight: 700;">#2</span>
                        </div>
                    </div>
                    <div style="text-align: center; width: 180px;">
                        <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $top3[0]['name'] }}</div>
                        <div style="color: #d4920c; font-size: 0.75rem; margin-bottom: 0.5rem;">{{ $top3[0]['total_orders'] }} orders</div>
                        <div style="background: linear-gradient(180deg, #e8b04a, #d4920c); height: {{ $podiumHeights[0] }}px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 1.75rem; font-weight: 700;">#1</span>
                        </div>
                    </div>
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
    @endif
</x-filament-panels::page>
