<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Recipe;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class PriceSuggestionTool extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Price Suggestion';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 8;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.price-suggestion-tool';

    public ?int $selectedRecipeId = null;

    public ?Recipe $selectedRecipe = null;

    public float $targetMarginPercentage = 65.0;

    public Collection $recipes;

    public Collection $marginComparisons;

    public function mount(): void
    {
        $this->recipes = Recipe::with('product')
            ->whereNotNull('cost')
            ->where('cost', '>', 0)
            ->orderBy('name')
            ->get();

        $this->generateMarginComparisons();
    }

    public function updatedSelectedRecipeId(): void
    {
        if ($this->selectedRecipeId) {
            $this->selectedRecipe = Recipe::with('product')->find($this->selectedRecipeId);
        } else {
            $this->selectedRecipe = null;
        }
        $this->generateMarginComparisons();
    }

    public function updatedTargetMarginPercentage(): void
    {
        $this->generateMarginComparisons();
    }

    public function generateMarginComparisons(): void
    {
        if (! $this->selectedRecipe || ! $this->selectedRecipe->cost) {
            $this->marginComparisons = collect();

            return;
        }

        $margins = [50, 60, 65, 70];

        $this->marginComparisons = collect($margins)->map(function (int $margin) {
            $suggestedPrice = $this->calculatePriceForMargin($margin);
            $currentPrice = $this->selectedRecipe->product->price ?? 0;
            $difference = $suggestedPrice - $currentPrice;

            return [
                'margin' => $margin,
                'price' => $suggestedPrice,
                'difference' => $difference,
                'difference_percentage' => $currentPrice > 0 ? (($difference / $currentPrice) * 100) : 0,
                'is_target' => $margin == $this->targetMarginPercentage,
            ];
        });
    }

    private function calculatePriceForMargin(float $marginPercentage): float
    {
        if (! $this->selectedRecipe || ! $this->selectedRecipe->cost || $marginPercentage <= 0) {
            return 0.0;
        }

        // Price = Cost / (1 - Margin/100)
        return $this->selectedRecipe->cost / (1 - ($marginPercentage / 100));
    }

    public function getSuggestedPrice(): float
    {
        return $this->calculatePriceForMargin($this->targetMarginPercentage);
    }

    public function getCurrentMargin(): ?float
    {
        if (! $this->selectedRecipe ||
            ! $this->selectedRecipe->product ||
            ! $this->selectedRecipe->product->price ||
            ! $this->selectedRecipe->cost) {
            return null;
        }

        $price = $this->selectedRecipe->product->price;
        $cost = $this->selectedRecipe->cost;

        return (($price - $cost) / $price) * 100;
    }

    public function getMarginAtCurrentPrice(): ?array
    {
        $currentMargin = $this->getCurrentMargin();

        if ($currentMargin === null) {
            return null;
        }

        $profit = $this->selectedRecipe->product->price - $this->selectedRecipe->cost;

        return [
            'margin' => $currentMargin,
            'profit' => $profit,
            'color' => $currentMargin >= 50 ? 'green' : ($currentMargin >= 30 ? 'yellow' : 'red'),
        ];
    }

    public function getPriceDifference(): ?array
    {
        if (! $this->selectedRecipe || ! $this->selectedRecipe->product) {
            return null;
        }

        $suggestedPrice = $this->getSuggestedPrice();
        $currentPrice = $this->selectedRecipe->product->price;
        $difference = $suggestedPrice - $currentPrice;

        return [
            'amount' => $difference,
            'percentage' => $currentPrice > 0 ? (($difference / $currentPrice) * 100) : 0,
            'direction' => $difference > 0 ? 'increase' : 'decrease',
        ];
    }
}
