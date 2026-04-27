@php
    $topCustomers = $this->getTopCustomers();
    $totalPoints = $this->getTotalPointsOutstanding();
    $recentAwards = $this->getRecentAwards();
    $topCount = $this->isSize('sm') ? 3 : 5;
    $shownCustomers = array_slice($topCustomers, 0, $topCount);
    $showAwards = ! $this->isSize('sm');
@endphp

<x-admin.dashboard.preview-card heading="Loyalty Leaders" icon="🏅">
    <x-admin.dashboard.stat-row label="Total Points Outstanding" :value="number_format($totalPoints)" class="mb-3" />

    @if (count($shownCustomers) > 0)
        <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase; margin-bottom: 4px;">Top {{ $topCount }} Members</div>
        @foreach ($shownCustomers as $i => $customer)
            <x-admin.dashboard.list-row :value="number_format($customer['points']).' pts'">
                <span @if ($i === 0) style="font-weight: 700; color: var(--pw-card-accent);" @else style="color: var(--pw-card-text);" @endif>
                    {{ $i + 1 }}. {{ $customer['name'] }}
                </span>
            </x-admin.dashboard.list-row>
        @endforeach
    @else
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">No loyalty points awarded yet</div>
    @endif

    @if ($showAwards && count($recentAwards) > 0)
        <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase; margin-top: 12px; padding-top: 8px; border-top: 1px solid var(--pw-card-border-subtle);">Recent Awards</div>
        @foreach ($recentAwards as $award)
            <x-admin.dashboard.list-row :value="$award['date']">
                <span style="color: var(--pw-card-text);">{{ $award['customer'] }}</span>
                <span style="color: var(--pw-card-accent); margin-left: 6px;">+{{ $award['points'] }} pts</span>
            </x-admin.dashboard.list-row>
        @endforeach
    @endif
</x-admin.dashboard.preview-card>
