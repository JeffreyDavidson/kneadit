@php
    $active = $this->getActiveCouponsCount();
    $redemptions = $this->getTotalRedemptions();
    $mostUsed = $this->getMostUsedCoupon();
    $expiring = $this->getExpiringSoonCount();
@endphp

<x-admin.dashboard.preview-card heading="Coupon Usage" icon="heroicon-o-ticket">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px">
        <x-admin.dashboard.stat-row label="Active" :value="$active" />
        <x-admin.dashboard.stat-row label="Redemptions" :value="$redemptions" />
    </div>

    @unless ($this->isSize('sm'))
        @if ($mostUsed)
            <div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid var(--pw-card-border-subtle)">
                <div
                    style="
                        font-size: 0.55rem;
                        color: var(--pw-card-text-muted);
                        text-transform: uppercase;
                        margin-bottom: 4px;
                    "
                >
                    Most Used
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline">
                    <span style="font-family: monospace; font-weight: 600; color: var(--pw-card-accent)">{{ $mostUsed->code }}</span>
                    <span style="font-size: 0.7rem; color: var(--pw-card-text-muted)">{{ $mostUsed->used_count }} uses</span>
                </div>
            </div>
        @endif

        @if ($expiring > 0)
            <div
                style="
                    margin-top: 8px;
                    padding: 6px 10px;
                    background: rgba(232, 176, 74, 0.15);
                    border: 1px solid rgba(232, 176, 74, 0.3);
                    border-radius: 6px;
                    font-size: 0.7rem;
                    color: var(--pw-card-text);
                    display: flex;
                    align-items: center;
                    gap: 4px;
                "
            >
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                {{ $expiring }} coupon{{ $expiring > 1 ? 's' : '' }} expiring within 7 days
            </div>
        @endif
    @endunless
</x-admin.dashboard.preview-card>
