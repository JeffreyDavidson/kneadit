@php
    $balance = $this->getTotalOutstandingBalance();
    $activeCards = $this->getActiveCardsCount();
    $recent = $this->getRecentlyRedeemed();
@endphp

<x-admin.dashboard.preview-card heading="Gift Cards" icon="heroicon-o-gift">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
        <div class="pw-stat">
            <span class="pw-stat-label">Outstanding</span>
            <span class="pw-stat-value">@money($balance)</span>
        </div>
        <x-admin.dashboard.stat-row label="Active Cards" :value="$activeCards" />
    </div>

    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--pw-card-border-subtle);">
        <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase; margin-bottom: 4px;">Recent Redemptions</div>
        @if (count($recent) > 0)
            @foreach ($recent as $txn)
                <x-admin.dashboard.list-row :value="'$'.number_format(abs($txn['amount_cents']) / 100, 2)">
                    <span style="font-family: monospace; font-weight: 600; color: var(--pw-card-accent);">{{ $txn['code'] }}</span>
                    <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $txn['date'] }}</span>
                </x-admin.dashboard.list-row>
            @endforeach
        @else
            <div style="text-align: center; padding: 8px 0; color: var(--pw-card-text-muted); font-size: 0.7rem; font-style: italic;">No recent redemptions</div>
        @endif
    </div>
</x-admin.dashboard.preview-card>
