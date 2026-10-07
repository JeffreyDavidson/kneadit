<?php

namespace App\Pipes\Orders;

use App\Services\Settings\TenantSettings;
use App\ValueObjects\Percentage;
use Closure;

/**
 * Applies a sitewide percentage discount to every order when the sale is
 * active. It does not stack with a coupon: ApplyCoupon, which runs next,
 * replaces the sale when the coupon is worth more.
 *
 * Placed AFTER CalculateOrderTotals (so subtotal is known) and BEFORE
 * ApplyCoupon. The discount is recorded against `discountAmount`; the
 * baker can configure a label for display via `sitewideSaleLabel`.
 */
class ApplySitewideSale
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $sale = $this->settings->orders;

        if (! $sale->sitewideSaleEnabled || $sale->sitewideSalePercent <= 0 || ! $payload->subtotal->isPositive()) {
            return $next($payload);
        }

        $percent = Percentage::fromInt(min(100, $sale->sitewideSalePercent));
        $saleDiscount = $percent->applyTo($payload->subtotal);

        $payload->sitewideSaleDiscount = $saleDiscount;
        $payload->recalculateDiscountAmount();
        $payload->recalculateTotal();

        return $next($payload);
    }
}
