@php
    use Illuminate\Support\Facades\Date;

    $days = [
        ['label' => 'Today', 'data' => $this->getTodayCapacity()],
        ['label' => 'Tomorrow', 'data' => $this->getTomorrowCapacity()],
        ['label' => Date::today()->copy()->addDays(2)->format('l'), 'data' => $this->getDayAfterCapacity()],
    ];

    $blocked = $this->isSize('sm') ? [] : $this->getBlockedDaysWarning();
@endphp

<x-admin.dashboard.preview-card heading="Order Capacity" icon="⏰">
    @foreach ($days as $day)
        @php
            $pct = $day['data']['percentage'];
            $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#e8b04a' : 'var(--pw-card-accent)');
        @endphp
        <div style="margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.7rem; margin-bottom: 4px;">
                <span style="font-weight: 600; color: var(--pw-card-text);">{{ $day['label'] }}</span>
                <span style="color: var(--pw-card-text-muted);">{{ $day['data']['current'] }} / {{ $day['data']['max'] }} ({{ $pct }}%)</span>
            </div>
            <div class="pw-bar"><div class="pw-bar-fill" style="width: {{ $pct }}%; background: {{ $barColor }};"></div></div>
        </div>
    @endforeach

    @if (count($blocked) > 0)
        <div style="margin-top: 10px; padding: 6px 10px; background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); border-radius: 6px;">
            <div style="font-size: 0.55rem; color: #d4574a; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Blocked Days This Week</div>
            @foreach ($blocked as $b)
                <div style="font-size: 0.7rem; color: var(--pw-card-text);">
                    <span style="font-weight: 600;">{{ $b['date'] }}</span> — {{ $b['reason'] }}
                </div>
            @endforeach
        </div>
    @endif
</x-admin.dashboard.preview-card>
