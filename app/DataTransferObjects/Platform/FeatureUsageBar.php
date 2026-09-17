<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Platform;

final readonly class FeatureUsageBar
{
    public function __construct(
        public string $feature,
        public int $total,
        public float $percent,
    ) {}

    /** @return array{feature: string, total: int, percent: float} */
    public function toArray(): array
    {
        return ['feature' => $this->feature, 'total' => $this->total, 'percent' => $this->percent];
    }
}
