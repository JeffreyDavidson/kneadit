<?php

namespace App\Filament\Widgets;

use App\Models\Coupon;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CouponUsageWidget extends Widget
{
    protected static ?int $sort = 15;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.coupon-usage-widget';

    public function getActiveCouponsCount(): int
    {
        return Coupon::where('is_active', true)->count();
    }

    public function getTotalRedemptions(): int
    {
        return (int) Coupon::sum('used_count');
    }

    public function getMostUsedCoupon(): ?Coupon
    {
        return Coupon::where('used_count', '>', 0)
            ->orderByDesc('used_count')
            ->first();
    }

    public function getExpiringSoonCount(): int
    {
        return Coupon::where('is_active', true)
            ->where('expires_at', '>=', Carbon::now())
            ->where('expires_at', '<=', Carbon::now()->addDays(7))
            ->count();
    }
}
