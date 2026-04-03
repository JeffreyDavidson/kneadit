<?php

namespace App\DataTransferObjects\Financial;

use Livewire\Wireable;

final readonly class PricingRecommendation implements Wireable
{
    /**
     * @param array<int, array{qty: int, label: string, unit_price: float, total: float}> $bulkTiers
     */
    public function __construct(
        public float $ingredientCost,
        public float $laborCost,
        public float $overhead,
        public float $totalCost,
        public float $recommendedPrice,
        public float $minPrice,
        public float $maxPrice,
        public ?float $currentPrice,
        public float $profitPerUnit,
        public float $actualMarginPercent,
        public array $bulkTiers,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return [
            'ingredientCost' => $this->ingredientCost,
            'laborCost' => $this->laborCost,
            'overhead' => $this->overhead,
            'totalCost' => $this->totalCost,
            'recommendedPrice' => $this->recommendedPrice,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'currentPrice' => $this->currentPrice,
            'profitPerUnit' => $this->profitPerUnit,
            'actualMarginPercent' => $this->actualMarginPercent,
            'bulkTiers' => $this->bulkTiers,
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    public static function fromLivewire($value): self
    {
        return new self(
            ingredientCost: (float) $value['ingredientCost'],
            laborCost: (float) $value['laborCost'],
            overhead: (float) $value['overhead'],
            totalCost: (float) $value['totalCost'],
            recommendedPrice: (float) $value['recommendedPrice'],
            minPrice: (float) $value['minPrice'],
            maxPrice: (float) $value['maxPrice'],
            currentPrice: isset($value['currentPrice']) ? (float) $value['currentPrice'] : null,
            profitPerUnit: (float) $value['profitPerUnit'],
            actualMarginPercent: (float) $value['actualMarginPercent'],
            bulkTiers: (array) $value['bulkTiers'],
        );
    }
}
