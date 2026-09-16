<?php

namespace App\DataTransferObjects\Analytics;

final readonly class ConversionFunnelStep
{
    public function __construct(
        public string $label,
        public int $count,
        public float $percentage,
        public ?float $dropoff,
    ) {}

    /** @return array{label: string, count: int, percentage: float, dropoff: float|null} */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'count' => $this->count,
            'percentage' => $this->percentage,
            'dropoff' => $this->dropoff,
        ];
    }
}
