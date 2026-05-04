@php
    $data = $this->getCardData();
    $trendColor = $data['trend'] >= 0 ? '#6b9e3a' : '#d4574a';
    $trendIcon = $data['trend'] >= 0 ? '↑' : '↓';
@endphp

<x-admin.dashboard.stat-card
    label="Storefront Views Today"
    icon="heroicon-o-building-storefront"
    :value="number_format($data['today'])"
    :description="$trendIcon . ' ' . abs($data['trend']) . '% vs yesterday'"
    :description-color="$trendColor"
    :chart="$data['chart']"
    :chart-height="36"
/>
