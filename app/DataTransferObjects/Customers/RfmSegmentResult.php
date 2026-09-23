<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Customers;

use App\Enums\Customers\RfmSegment;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class RfmSegmentResult implements Arrayable
{
    /**
     * @param  list<RfmCustomerSample>  $sampleCustomers
     */
    public function __construct(
        public RfmSegment $segment,
        public string $label,
        public string $description,
        public string $color,
        public int $count,
        public array $sampleCustomers,
    ) {}

    /**
     * @return array{
     *     segment: RfmSegment,
     *     label: string,
     *     description: string,
     *     color: string,
     *     count: int,
     *     sampleCustomers: list<array{id: int, name: string, email: string, recency_days: int, frequency: int, monetary: float}>
     * }
     */
    public function toArray(): array
    {
        return [
            'segment' => $this->segment,
            'label' => $this->label,
            'description' => $this->description,
            'color' => $this->color,
            'count' => $this->count,
            'sampleCustomers' => array_map(
                static fn (RfmCustomerSample $customer): array => $customer->toArray(),
                $this->sampleCustomers,
            ),
        ];
    }
}
