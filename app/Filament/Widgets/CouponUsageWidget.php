<?php

namespace App\Filament\Widgets;

use App\Models\Financial\Coupon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CouponUsageWidget extends Widget
{
    protected static ?int $sort = 15;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.coupon-usage-widget';

    public function getActiveCouponsCount(): int
    {
        return Coupon::query()->where('is_active', true)->count();
    }

    public function getTotalRedemptions(): int
    {
        return (int) Coupon::query()->sum('used_count');
    }

    public function getMostUsedCoupon(): ?Coupon
    {
        return Coupon::query()->where('used_count', '>', 0)
            ->orderByDesc('used_count')
            ->first();
    }

    public function getExpiringSoonCount(): int
    {
        return Coupon::query()->where('is_active', true)
            ->where('expires_at', '>=', Date::now())
            ->where('expires_at', '<=', Date::now()->addDays(7))
            ->count();
    }
}
