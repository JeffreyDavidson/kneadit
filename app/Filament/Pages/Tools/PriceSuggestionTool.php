<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Recipe;
use App\Services\Financial\PricingRecommendationService;
use App\Services\Financial\ProductAnalysisService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class PriceSuggestionTool extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Price Suggestion';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 8;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.tools.price-suggestion-tool';

    public ?int $selectedRecipeId = null;

    public ?Recipe $selectedRecipe = null;

    public float $targetMarginPercentage = 65.0;

    /** @var Collection<int, mixed> */
    public Collection $recipes;

    /** @var Collection<int, mixed> */
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

        $pricing = $this->pricingService();
        $margins = [50, 60, 65, 70];
        $recipe = $this->selectedRecipe;

        $this->marginComparisons = collect($margins)->map(function (int $margin) use ($pricing, $recipe) {
            $suggestedPrice = $pricing->suggestPrice($recipe->cost?->dollars() ?? 0.0, $margin);
            $currentPrice = $recipe->product?->price?->dollars() ?? 0.0;
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

    public function getSuggestedPrice(): float
    {
        if (! $this->selectedRecipe || ! $this->selectedRecipe->cost) {
            return 0.0;
        }

        return $this->pricingService()->suggestPrice(
            $this->selectedRecipe->cost->dollars(),
            $this->targetMarginPercentage,
        );
    }

    public function getCurrentMargin(): ?float
    {
        if (! $this->selectedRecipe?->product) {
            return null;
        }

        $analysis = $this->analysisService()->analyze(
            $this->selectedRecipe->product,
            $this->targetMarginPercentage,
        );

        return $analysis->currentMarginPercent;
    }

    /** @return array<string, mixed>|null */
    public function getMarginAtCurrentPrice(): ?array
    {
        if (! $this->selectedRecipe?->product) {
            return null;
        }

        $analysis = $this->analysisService()->analyze(
            $this->selectedRecipe->product,
            $this->targetMarginPercentage,
        );

        if ($analysis->currentMarginPercent === null) {
            return null;
        }

        return [
            'margin' => $analysis->currentMarginPercent,
            'profit' => $analysis->profitPerUnit,
            'color' => $analysis->marginHealth->cssClass(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function getPriceDifference(): ?array
    {
        if (! $this->selectedRecipe?->product) {
            return null;
        }

        $suggestedPrice = $this->getSuggestedPrice();
        $currentPrice = $this->selectedRecipe->product->price?->dollars() ?? 0.0;
        $difference = $suggestedPrice - $currentPrice;

        return [
            'amount' => $difference,
            'percentage' => $currentPrice > 0 ? (($difference / $currentPrice) * 100) : 0,
            'direction' => $difference > 0 ? 'increase' : 'decrease',
        ];
    }

    private function pricingService(): PricingRecommendationService
    {
        return resolve(PricingRecommendationService::class);
    }

    private function analysisService(): ProductAnalysisService
    {
        return resolve(ProductAnalysisService::class);
    }
}
