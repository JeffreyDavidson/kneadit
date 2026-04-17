<?php

namespace App\DataTransferObjects\Financial;

use Illuminate\Support\Collection;

final readonly class ProductPortfolioSummary
{
    /**
     * @param Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> $products
     * @param array{high: int, medium: int, low: int} $marginBreakdown
     */
    public function __construct(
        public Collection $products,
        public int $totalProducts,
        public int $productsWithCosts,
        public ?float $averageMargin,
        public array $marginBreakdown,
        public float $totalRevenuePotential,
        public float $totalCosts,
        public float $totalProfitPotential,
        public float $overallMarginPercent,
    ) {}

    public function productsMissingCosts(): int
    {
        return $this->totalProducts - $this->productsWithCosts;
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function topProfitable(int $limit = 5): Collection
    {
        return $this->products
            ->where('has_cost_data', true)
            ->where('margin_amount', '>', 0)
            ->sortByDesc('margin_amount')
            ->take($limit);
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function lowestMargin(int $limit = 5): Collection
    {
        return $this->products
            ->where('has_cost_data', true)
            ->whereNotNull('margin_percentage')
            ->sortBy('margin_percentage')
            ->take($limit);
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function missingCosts(): Collection
    {
        return $this->products
            ->where('has_cost_data', false)
            ->sortBy('name');
    }
}
