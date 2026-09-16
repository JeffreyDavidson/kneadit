<?php

namespace App\DataTransferObjects\Analytics;

final readonly class TopViewedProduct
{
    public function __construct(public string $name, public int $views) {}
}
