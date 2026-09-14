<?php

namespace App\DataTransferObjects\Analytics;

final readonly class DailyPageViewCount
{
    public function __construct(public string $date, public int $views) {}
}
