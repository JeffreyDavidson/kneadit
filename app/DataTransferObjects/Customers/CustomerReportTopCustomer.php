<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Customers;

use App\ValueObjects\Money;

final readonly class CustomerReportTopCustomer
{
    public function __construct(
        public string $name,
        public string $email,
        public Money $totalSpend,
        public int $orderCount,
    ) {}

    /** @return array{name: string, email: string, total_spend: float, order_count: int} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'total_spend' => $this->totalSpend->dollars(),
            'order_count' => $this->orderCount,
        ];
    }
}
