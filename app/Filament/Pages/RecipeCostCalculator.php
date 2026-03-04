<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Support\Collection;

class RecipeCostCalculator extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Recipe Cost Calculator';
    protected static string|\UnitEnum|null $navigationGroup = 'Kitchen';
    protected string $view = 'filament.pages.recipe-cost-calculator';

    public ?int $selectedRecipeId = null;
    public ?Recipe $selectedRecipe = null;
    public float $targetMarginPercentage = 65.0;
    public float $totalRecipeCost = 0.0;
    public float $currentMargin = 0.0;
    public float $suggestedPrice = 0.0;
    public Collection $recipes;

    public function mount()
    {
        $this->recipes = Recipe::with('product')->orderBy('name')->get();
    }

    public function updatedSelectedRecipeId()
    {
        if ($this->selectedRecipeId) {
            $this->selectedRecipe = Recipe::with('product')->find($this->selectedRecipeId);
            $this->calculateCosts();
        } else {
            $this->selectedRecipe = null;
            $this->resetCalculations();
        }
    }

    public function updatedTargetMarginPercentage()
    {
        if ($this->selectedRecipe) {
            $this->calculateSuggestedPrice();
        }
    }

    public function calculateCosts()
    {
        if (!$this->selectedRecipe || !$this->selectedRecipe->ingredients) {
            $this->resetCalculations();
            return;
        }

        $this->totalRecipeCost = 0.0;

        foreach ($this->selectedRecipe->ingredients as $ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;
            $this->totalRecipeCost += ($cost * $quantity);
        }

        $this->calculateCurrentMargin();
        $this->calculateSuggestedPrice();
    }

    private function calculateCurrentMargin()
    {
        if ($this->selectedRecipe && $this->selectedRecipe->product && $this->selectedRecipe->product->price && $this->totalRecipeCost > 0) {
            $productPrice = $this->selectedRecipe->product->price;
            $this->currentMargin = (($productPrice - $this->totalRecipeCost) / $productPrice) * 100;
        } else {
            $this->currentMargin = 0.0;
        }
    }

    private function calculateSuggestedPrice()
    {
        if ($this->totalRecipeCost > 0 && $this->targetMarginPercentage > 0) {
            // Price = Cost / (1 - Margin/100)
            $this->suggestedPrice = $this->totalRecipeCost / (1 - ($this->targetMarginPercentage / 100));
        } else {
            $this->suggestedPrice = 0.0;
        }
    }

    private function resetCalculations()
    {
        $this->totalRecipeCost = 0.0;
        $this->currentMargin = 0.0;
        $this->suggestedPrice = 0.0;
    }

    public function getFormattedIngredients(): Collection
    {
        if (!$this->selectedRecipe || !$this->selectedRecipe->ingredients) {
            return collect();
        }

        return collect($this->selectedRecipe->ingredients)->map(function ($ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;
            $totalCost = $cost * $quantity;

            return [
                'name' => $ingredient['name'] ?? '',
                'quantity' => $quantity,
                'unit' => $ingredient['unit'] ?? '',
                'cost_per_unit' => $cost,
                'total_cost' => $totalCost,
            ];
        });
    }
}