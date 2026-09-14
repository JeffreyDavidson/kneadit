<?php

namespace App\DataTransferObjects\Platform;

final readonly class FeatureUsageHeatmap
{
    /**
     * @param list<string> $days
     * @param list<array{feature: string, cells: list<array{date: string, count: int, intensity: float}>}> $rows
     */
    public function __construct(
        public array $days,
        public array $rows,
    ) {}

    /** @return array{days: list<string>, rows: list<array{feature: string, cells: list<array{date: string, count: int, intensity: float}>}>} */
    public function toArray(): array
    {
        return ['days' => $this->days, 'rows' => $this->rows];
    }
}
