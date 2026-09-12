<?php

namespace App\DataTransferObjects\Customers;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class CustomerReportResult implements Arrayable
{
    /**
     * @param list<array{name: string, email: string, total_spend: Money, order_count: int}> $topCustomers
     * @param array<string, int> $acquisitionByMonth
     */
    public function __construct(
        public int $newCustomers,
        public float $repeatRate,
        public int $repeatCustomers,
        public int $totalCustomersWithOrders,
        public array $topCustomers,
        public array $acquisitionByMonth,
    ) {}

    /**
     * @return array{
     *     newCustomers: int,
     *     repeatRate: float,
     *     repeatCustomers: int,
     *     totalCustomersWithOrders: int,
     *     topCustomers: list<array{name: string, email: string, total_spend: float, order_count: int}>,
     *     acquisitionByMonth: array<string, int>
     * }
     */
    public function toArray(): array
    {
        return [
            'newCustomers' => $this->newCustomers,
            'repeatRate' => $this->repeatRate,
            'repeatCustomers' => $this->repeatCustomers,
            'totalCustomersWithOrders' => $this->totalCustomersWithOrders,
            'topCustomers' => array_map(static fn (array $customer): array => [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'total_spend' => $customer['total_spend']->dollars(),
                'order_count' => $customer['order_count'],
            ], $this->topCustomers),
            'acquisitionByMonth' => $this->acquisitionByMonth,
        ];
    }
}
