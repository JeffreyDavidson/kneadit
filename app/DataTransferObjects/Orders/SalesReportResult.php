<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Orders;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class SalesReportResult implements Arrayable
{
    /**
     * @param  array<string, int>  $ordersByStatus
     * @param  list<SalesReportProduct>  $topProducts
     * @param  list<SalesReportDay>  $revenueByDay
     */
    public function __construct(
        public int $totalOrders,
        public Money $totalRevenue,
        public Money $averageOrderValue,
        public array $ordersByStatus,
        public array $topProducts,
        public array $revenueByDay,
    ) {}

    /**
     * @return array{
     *     totalOrders: int,
     *     totalRevenue: float,
     *     avgOrderValue: float,
     *     ordersByStatus: array<string, int>,
     *     topProducts: list<array{name: string, units_sold: int, revenue: float}>,
     *     revenueByDay: list<array{date: string, revenue: float}>
     * }
     */
    public function toArray(): array
    {
        return [
            'totalOrders' => $this->totalOrders,
            'totalRevenue' => $this->totalRevenue->dollars(),
            'avgOrderValue' => $this->averageOrderValue->dollars(),
            'ordersByStatus' => $this->ordersByStatus,
            'topProducts' => array_map(
                static fn (SalesReportProduct $product): array => $product->toArray(),
                $this->topProducts,
            ),
            'revenueByDay' => array_map(
                static fn (SalesReportDay $day): array => $day->toArray(),
                $this->revenueByDay,
            ),
        ];
    }
}
