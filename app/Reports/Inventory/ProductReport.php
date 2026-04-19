<?php

namespace App\Reports\Inventory;

use App\Enums\Orders\PaymentStatus;
use App\Models\Inventory\Product;
use App\Support\ProfitMargin;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class ProductReport
{
    /** @return array<string, mixed> */
    public function generate(DateRange $range): array
    {
        $products = Product::query()->withSum(['orderItems as units_sold' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid))], 'quantity')
            ->withSum(['orderItems as revenue' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid))], DB::raw('quantity * unit_price'))
            ->get()
            ->map(function (Product $p): array {
                $price = $p->price?->dollars() ?? 0.0;
                $cost = $p->cost?->dollars() ?? 0.0;
                $margin = $cost > 0
                    ? ProfitMargin::calculate($price, $cost, 1)
                    : null;

                return [
                    'name' => $p->name,
                    'price' => $price,
                    'cost' => $cost,
                    'units_sold' => (int) ($p->units_sold ?? 0),
                    'revenue' => (float) ($p->revenue ?? 0),
                    'margin' => $margin,
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->all();

        return ['products' => $products];
    }
}
