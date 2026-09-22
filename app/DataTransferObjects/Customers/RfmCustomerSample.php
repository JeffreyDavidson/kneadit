<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Customers;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class RfmCustomerSample implements Arrayable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public int $recencyDays,
        public int $frequency,
        public Money $monetary,
    ) {}

    /** @return array{id: int, name: string, email: string, recency_days: int, frequency: int, monetary: float} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'recency_days' => $this->recencyDays,
            'frequency' => $this->frequency,
            'monetary' => $this->monetary->dollars(),
        ];
    }
}
