<?php

namespace App\Reports\Orders;

use App\DataTransferObjects\Orders\SalesReportResult;
use App\Models\Orders\Order;
use App\Queries\Financial\ProductSalesQuery;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SalesReport
{
    public function generate(DateRange $range): SalesReportResult
    {
        $orders = Order::query()
            ->active()
            ->paid()
            ->whereBetween('delivery_date', $range->toArray());

        $totalOrders = $orders->count();
        $totalRevenue = RevenueQuery::total($range);
        $averageOrderValue = $totalOrders > 0
            ? $totalRevenue->multiply(1 / $totalOrders)
            : Money::zero();

        $ordersByStatus = (clone $orders)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->mapWithKeys(static fn (mixed $count, mixed $status): array => [
                Arr::string(['status' => $status], 'status') => Arr::integer(['count' => $count], 'count'),
            ])
            ->all();

        $topProducts = array_values(ProductSalesQuery::topByRevenue($range)
            ->map(static fn (array $product): array => [
                'name' => $product['name'],
                'units_sold' => $product['units_sold'],
                'revenue' => Money::fromDollars($product['revenue']),
            ])
            ->all());

        $revenueByDay = array_values((clone $orders)
            ->select(DB::raw('DATE(delivery_date) as date'), DB::raw('SUM(total) as revenue_cents'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function (Order $row): array {
                $date = $row->getAttribute('date');

                return [
                    'date' => is_string($date) ? $date : '',
                    'revenue' => Money::fromCents(Arr::integer($row->getAttributes(), 'revenue_cents', 0)),
                ];
            })
            ->all());

        return new SalesReportResult(
            totalOrders: $totalOrders,
            totalRevenue: $totalRevenue,
            averageOrderValue: $averageOrderValue,
            ordersByStatus: $ordersByStatus,
            topProducts: $topProducts,
            revenueByDay: $revenueByDay,
        );
    }
}
