@php
    $data = $this->getCardData();
    $trendColor = $data['trend'] >= 0 ? '#6b9e3a' : '#d4574a';
    $trendIcon = $data['trend'] >= 0 ? '↑' : '↓';
@endphp

<x-admin.dashboard.preview-card heading="Storefront Views" icon="🏪">
    <div class="pw-stat">
        <span class="pw-stat-label">Today</span>
        <span class="pw-stat-value">{{ number_format($data['today']) }}</span>
    </div>
    <div style="font-size: 0.65rem; color: {{ $trendColor }}; margin-top: 2px;">
        {{ $trendIcon }} {{ abs($data['trend']) }}% vs yesterday
    </div>
    <x-admin.dashboard.spark-bars :bars="$data['chart']" :height="32" class="mt-2" />
    <div style="margin-top: 4px; text-align: right;">
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.6rem; color: var(--pw-card-accent); text-decoration: none;">Analytics →</a>
    </div>
</x-admin.dashboard.preview-card>
