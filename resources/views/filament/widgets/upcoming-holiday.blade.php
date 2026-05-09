@php $h = $this->getHolidayData(); @endphp

<div>
    @if ($h)
        <x-admin.dashboard.preview-card heading="Upcoming Holiday" icon="heroicon-o-calendar">
            <div class="pw-stat">
                <span class="pw-stat-label">{{ $h['name'] }}</span>
                <span class="pw-stat-value">{{ $h['date'] }}</span>
            </div>
            <div style="margin-top: 6px; font-size: 0.7rem; color: var(--pw-card-text-muted);">
                {{ $h['orders'] }}{{ $h['max_orders'] ? ' / '.$h['max_orders'].' max' : '' }} order{{ $h['orders'] === 1 ? '' : 's' }}
            </div>
            <div style="margin-top: 2px; font-size: 0.65rem; color: {{ $h['is_urgent'] ? '#e8b04a' : ($h['deadline_passed'] ? '#d4574a' : 'var(--pw-card-text-muted)') }};">
                @if ($h['deadline_passed'])
                    <span style="display: inline-flex; align-items: center; gap: 3px;">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-3.5 w-3.5" />
                        Deadline passed
                    </span>
                @else
                    Deadline in {{ $h['days_until_deadline'] }}d
                @endif
            </div>
        </x-admin.dashboard.preview-card>
    @endif
</div>
