<?php

namespace App\DataTransferObjects\Analytics;

final readonly class PageViewCount
{
    public function __construct(public string $page, public int $views) {}
}
