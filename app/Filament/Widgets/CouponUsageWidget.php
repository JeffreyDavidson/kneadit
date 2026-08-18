<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Financial\Coupon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CouponUsageWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 15;

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
        // Cache the id, not the model. Cache stores hydrate as __PHP_Incomplete_Class
        // because config(cache.serializable_classes) is false. Same shape as #302.
        $id = $this->cached('most_used_id', [300, 600], function (): ?int {
            $id = Coupon::query()->where('used_count', '>', 0)
                ->orderByDesc('used_count')
                ->value('id');

            return is_numeric($id) ? (int) $id : null;
        });

        return $id ? Coupon::query()->whereKey($id)->first() : null;
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
