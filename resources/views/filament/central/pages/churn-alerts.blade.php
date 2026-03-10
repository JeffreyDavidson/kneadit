<x-filament-panels::page>
    @php
        $alerts = $this->getAlerts();
    @endphp

    @if ($alerts->isEmpty())
        <div style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
            <div style="font-size: 1.25rem; font-weight: 600; color: #10b981;">All bakeries are healthy!</div>
            <div style="color: #a8947c; margin-top: 0.5rem;">No churn alerts at this time.</div>
        </div>
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
                <div style="background: #1c1410; border: 1px solid #2a1f18; border-radius: 12px; padding: 1.25rem 1.5rem; border-left: 4px solid {{ $badgeColor }};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="flex: 1; min-width: 200px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                <span style="font-weight: 600; color: #f5d88e; font-size: 1.05rem;">{{ $alert['name'] }}</span>
                                <span style="display: inline-block; background: {{ $tb['bg'] }}; color: {{ $tb['color'] }}; border-radius: 9999px; padding: 0.15rem 0.6rem; font-size: 0.7rem; font-weight: 600;">
                                    {{ $alert['type_label'] }}
                                </span>
                            </div>
                            <div style="color: #a8947c; font-size: 0.85rem;">{{ $alert['description'] }}</div>
                            <div style="color: #6b5c4e; font-size: 0.75rem; margin-top: 0.35rem;">Signed up {{ $alert['days_since_signup'] }} days ago</div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-shrink: 0;">
                            <button style="background: #2a1f18; color: #d4920c; border: 1px solid #d4920c; border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;" onclick="alert('Coming soon')">Extend Trial</button>
                            <button style="background: #2a1f18; color: #e8b04a; border: 1px solid #e8b04a; border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;" onclick="alert('Coming soon')">Send Nudge</button>
                            <a href="{{ $this->getViewTenantUrl($alert['tenant_id']) }}" style="background: #d4920c; color: #1c1410; border: none; border-radius: 8px; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block;">View Tenant</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
