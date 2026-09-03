<?php

namespace App\Reports\Inventory;

use App\DataTransferObjects\Inventory\ProductReportResult;
use App\Enums\Orders\PaymentStatus;
use App\Models\Inventory\Product;
use App\Support\ProfitMargin;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductReport
{
    public function generate(DateRange $range): ProductReportResult
    {
        $products = array_values(Product::query()->withSum(['orderItems as units_sold' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid))], 'quantity')
            ->withSum(['orderItems as revenue' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid))], DB::raw('quantity * unit_price'))
            ->get()
            ->map(function (Product $p): array {
                $price = $p->price ?? Money::zero();
                $cost = $p->cost ?? Money::zero();
                $margin = $cost->isPositive()
                    ? ProfitMargin::calculate($price->dollars(), $cost->dollars(), 1)
                    : null;

                return [
                    'name' => $p->name,
                    'price' => $price,
                    'cost' => $cost,
                    'units_sold' => Arr::integer(['value' => $p->units_sold ?? 0], 'value', 0),
                    // unit_price is bigint cents (migration 2026_04_22_201500).
                    'revenue' => Money::fromCents(Arr::integer(['value' => $p->revenue ?? 0], 'value', 0)),
                    'margin' => $margin,
                ];
            })
            ->sortByDesc(fn (array $product): int => $product['revenue']->cents())
            ->values()
            ->all());

        return new ProductReportResult(products: $products);
    }
}
