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

    /**
     * @param  array{mrr: int, payingCount: int, arpu: float, churnedCount: int, churnRate: float, trialedCount: int, convertedCount: int, trialConversion: float}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mrr: $data['mrr'],
            payingCount: $data['payingCount'],
            arpu: $data['arpu'],
            churnedCount: $data['churnedCount'],
            churnRate: $data['churnRate'],
            trialedCount: $data['trialedCount'],
            convertedCount: $data['convertedCount'],
            trialConversion: $data['trialConversion'],
        );
    }

    /**
     * @return array{mrr: int, payingCount: int, arpu: float, churnedCount: int, churnRate: float, trialedCount: int, convertedCount: int, trialConversion: float}
     */
    public function toArray(): array
    {
        return [
            'mrr' => $this->mrr,
            'payingCount' => $this->payingCount,
            'arpu' => $this->arpu,
            'churnedCount' => $this->churnedCount,
            'churnRate' => $this->churnRate,
            'trialedCount' => $this->trialedCount,
            'convertedCount' => $this->convertedCount,
            'trialConversion' => $this->trialConversion,
        ];
    }
}
