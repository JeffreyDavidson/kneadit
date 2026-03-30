<x-filament-widgets::widget>
    <x-filament::section heading="Coupon Usage" icon="heroicon-o-ticket">
        @php
            $active = $this->getActiveCouponsCount();
            $redemptions = $this->getTotalRedemptions();
            $mostUsed = $this->getMostUsedCoupon();
            $expiring = $this->getExpiringSoonCount();
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $active }}</div>
                <div style="font-size: 0.75rem; color: #6b4c3b;">Active Coupons</div>
            </div>
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $redemptions }}</div>
                <div style="font-size: 0.75rem; color: #6b4c3b;">Total Redemptions</div>
            </div>
        </div>

        <div style="margin-top: 16px; padding: 12px; background: #fdf8f2; border-radius: 8px;">
            <div style="font-size: 0.75rem; color: #6b4c3b; margin-bottom: 4px;">Most Used Coupon</div>
            @if ($mostUsed)
                <div style="font-weight: 600; color: #3d2314;">{{ $mostUsed->code }}</div>
                <div style="font-size: 0.75rem; color: #8b6844;">{{ $mostUsed->used_count }} uses</div>
            @else
                <div style="color: #8b6844; font-style: italic;">No coupons redeemed yet</div>
            @endif
        </div>

        @if ($expiring > 0)
            <div style="margin-top: 12px; padding: 10px 12px; background: #e8b04a20; border: 1px solid #e8b04a40; border-radius: 8px; font-size: 0.8rem; color: #6b4c3b;">
                {{ $expiring }} coupon{{ $expiring > 1 ? 's' : '' }} expiring within 7 days
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
