<?php

namespace App\DataTransferObjects\Tenants;

final readonly class TenantHealthSummary
{
    public function __construct(
        public int|float $average,
        public int $healthy,
        public int $atRisk,
        public int $critical,
        public int $total,
    ) {}

    /** @return array{average: int|float, healthy: int, at_risk: int, critical: int, total: int} */
    public function toArray(): array
    {
        return [
            'average' => $this->average,
            'healthy' => $this->healthy,
            'at_risk' => $this->atRisk,
            'critical' => $this->critical,
            'total' => $this->total,
        ];
    }
}
