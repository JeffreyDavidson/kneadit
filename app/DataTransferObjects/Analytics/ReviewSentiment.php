<?php

namespace App\DataTransferObjects\Analytics;

final readonly class ReviewSentiment
{
    public function __construct(
        public float $positive,
        public float $neutral,
        public float $negative,
    ) {}

    /** @return array{positive: float, neutral: float, negative: float} */
    public function toArray(): array
    {
        return [
            'positive' => $this->positive,
            'neutral' => $this->neutral,
            'negative' => $this->negative,
        ];
    }
}
