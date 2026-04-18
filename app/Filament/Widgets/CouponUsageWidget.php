<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Financial\Coupon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CouponUsageWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 15;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.coupon-usage-widget';

    public function getActiveCouponsCount(): int
    {
        return $this->cached('active_count', [300, 600], fn (): int => Coupon::query()->where('is_active', true)->count());
    }

    public function getTotalRedemptions(): int
    {
        return $this->cached('total_redemptions', [300, 600], fn (): int => (int) Coupon::query()->sum('used_count'));
    }

    public function getMostUsedCoupon(): ?Coupon
    {
        return $this->cached('most_used', [300, 600], fn (): ?Coupon => Coupon::query()->where('used_count', '>', 0)
            ->orderByDesc('used_count')
            ->first());
    }

    public function getExpiringSoonCount(): int
    {
        return $this->cached('expiring_soon', [300, 600], fn (): int => Coupon::query()->where('is_active', true)
            ->where('expires_at', '>=', Date::now())
            ->where('expires_at', '<=', Date::now()->addDays(7))
            ->count());
    }

    protected function cachePrefix(): string
    {
        return 'coupon_usage';
    }
}
