<x-filament-widgets::widget>
    <x-filament::section heading="Coupon Usage" icon="heroicon-o-ticket">
        @php
            $active = $this->getActiveCouponsCount();
            $redemptions = $this->getTotalRedemptions();
            $mostUsed = $this->getMostUsedCoupon();
            $expiring = $this->getExpiringSoonCount();
        @endphp

        <div class="grid grid-cols-2 gap-4">
            <x-admin.stat-cell label="Active Coupons">{{ $active }}</x-admin.stat-cell>
            <x-admin.stat-cell label="Total Redemptions">{{ $redemptions }}</x-admin.stat-cell>
        </div>

        {{-- sm: just the two stats above. md: + most-used coupon + expiring warning. --}}
        @unless ($this->isSize('sm'))
            <div class="mt-4 p-3 bg-brand-50 rounded-lg">
                <div class="text-xs text-brand-700 mb-1">Most Used Coupon</div>
                @if ($mostUsed)
                    <div class="font-semibold text-brand-900">{{ $mostUsed->code }}</div>
                    <div class="text-xs text-brand-600">{{ $mostUsed->used_count }} uses</div>
                @else
                    <div class="text-brand-600 italic">No coupons redeemed yet</div>
                @endif
            </div>

            @if ($expiring > 0)
                <div class="mt-3 px-3 py-2.5 bg-golden/20 border border-golden/40 rounded-lg text-[0.8rem] text-brand-700">
                    {{ $expiring }} coupon{{ $expiring > 1 ? 's' : '' }} expiring within 7 days
                </div>
            @endif
        @endunless
    </x-filament::section>
</x-filament-widgets::widget>
