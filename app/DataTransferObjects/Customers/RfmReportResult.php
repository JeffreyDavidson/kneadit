<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Customers;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class RfmReportResult implements Arrayable
{
    /**
     * @param  array<string, RfmSegmentResult>  $segments
     */
    public function __construct(
        public int $total,
        public array $segments,
    ) {}

    /**
     * @return array{
     *     total: int,
     *     segments: array<string, array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'segments' => array_map(
                static fn (RfmSegmentResult $segment): array => $segment->toArray(),
                $this->segments,
            ),
        ];
    }
}
