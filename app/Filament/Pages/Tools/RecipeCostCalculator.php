<?php

namespace App\Filament\Pages\Tools;

use App\DataTransferObjects\Financial\ProductCostAnalysis;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Recipe;
use App\Services\Financial\ProductAnalysisService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class RecipeCostCalculator extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Recipe Cost Calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.tools.recipe-cost-calculator';

    public ?int $selectedRecipeId = null;

    public ?Recipe $selectedRecipe = null;

    public float $targetMarginPercentage = 65.0;

    public ?ProductCostAnalysis $analysis = null;

    /** @var Collection<int, mixed> */
    public Collection $recipes;

    public function mount(): void
    {
        $this->recipes = Recipe::with('product')->orderBy('name')->get();
    }

    public function updatedSelectedRecipeId(): void
    {
        if ($this->selectedRecipeId) {
            $this->selectedRecipe = Recipe::with('product')->find($this->selectedRecipeId);
            $this->refreshAnalysis();
        } else {
            $this->selectedRecipe = null;
            $this->analysis = null;
        }
    }

    public function updatedTargetMarginPercentage(): void
    {
        $this->refreshAnalysis();
    }

    public function refreshAnalysis(): void
    {
        if (! $this->selectedRecipe?->product) {
            $this->analysis = null;

            return;
        }

        $this->analysis = resolve(ProductAnalysisService::class)
            ->analyze($this->selectedRecipe->product, $this->targetMarginPercentage);
    }
}
