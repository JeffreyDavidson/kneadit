<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Recipe;
use App\Services\Financial\RecipeCostService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class RecipeCostCalculator extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Recipe Cost Calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.tools.recipe-cost-calculator';

    public ?int $selectedRecipeId = null;

    public ?Recipe $selectedRecipe = null;

    public float $targetMarginPercentage = 65.0;

    public float $totalRecipeCost = 0.0;

    public float $currentMargin = 0.0;

    public float $suggestedPrice = 0.0;

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
            $this->calculateCosts();
        } else {
            $this->selectedRecipe = null;
            $this->resetCalculations();
        }
    }

    public function updatedTargetMarginPercentage(): void
    {
        if ($this->selectedRecipe) {
            $service = resolve(RecipeCostService::class);
            $this->suggestedPrice = $service->calculateSuggestedPrice($this->totalRecipeCost, $this->targetMarginPercentage);
        }
    }

    public function calculateCosts(): void
    {
        $service = resolve(RecipeCostService::class);

        $this->totalRecipeCost = $service->calculateCosts($this->selectedRecipe);

        if ($this->totalRecipeCost <= 0) {
            $this->resetCalculations();

            return;
        }

        $this->currentMargin = $service->calculateCurrentMargin($this->selectedRecipe, $this->totalRecipeCost);
        $this->suggestedPrice = $service->calculateSuggestedPrice($this->totalRecipeCost, $this->targetMarginPercentage);
    }

    private function resetCalculations(): void
    {
        $this->totalRecipeCost = 0.0;
        $this->currentMargin = 0.0;
        $this->suggestedPrice = 0.0;
    }

    /** @return Collection<int, mixed> */
    public function getFormattedIngredients(): Collection
    {
        return resolve(RecipeCostService::class)->getFormattedIngredients($this->selectedRecipe);
    }
}
