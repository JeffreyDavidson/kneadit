<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Platform;

final readonly class PlatformRevenueMetrics
{
    public function __construct(
        public int $mrr,
        public int $payingCount,
        public float $arpu,
        public int $churnedCount,
        public float $churnRate,
        public int $trialedCount,
        public int $convertedCount,
        public float $trialConversion,
    ) {}
}
