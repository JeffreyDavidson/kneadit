<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;

class PricingEngine extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Pricing Engine';
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 15;
    protected string $view = 'filament.pages.pricing-engine';

    public ?string $selectedProductId = null;
    public float $ingredientCost = 0;
    public float $prepTimeMinutes = 0;
    public float $hourlyLaborRate = 15;
    public float $overheadPercentage = 20;
    public int $targetProfitMargin = 50;
    public string $positioning = 'standard';
    public ?array $result = null;

    public function mount(): void
    {
        $this->hourlyLaborRate = (float) (Setting::where('key', 'hourly_labor_rate')->value('value') ?? 15);
        $this->overheadPercentage = (float) (Setting::where('key', 'overhead_percentage')->value('value') ?? 20);
        $this->targetProfitMargin = (int) (Setting::where('key', 'target_profit_margin')->value('value') ?? 50);
    }

    public function getProductsProperty(): \Illuminate\Support\Collection
    {
        return Product::query()->with(['category', 'recipes'])->orderBy('name')->get();
    }

    public function updatedSelectedProductId(): void
    {
        if (!$this->selectedProductId) {
            $this->ingredientCost = 0;
            $this->prepTimeMinutes = 0;
            return;
        }

        $product = Product::with('recipes')->find($this->selectedProductId);
        if (!$product) return;

        // Pull cost from recipe if available
        $recipe = $product->recipes->first();
        if ($recipe) {
            $this->ingredientCost = (float) ($recipe->cost ?? $product->cost ?? 0);
            $this->prepTimeMinutes = (float) ($recipe->prep_time_minutes ?? 0);
        } else {
            $this->ingredientCost = (float) ($product->cost ?? 0);
            $this->prepTimeMinutes = 0;
        }
    }

    public function calculate(): void
    {
        $laborCost = ($this->prepTimeMinutes / 60) * $this->hourlyLaborRate;
        $baseCost = $this->ingredientCost + $laborCost;
        $overheadAmount = $baseCost * ($this->overheadPercentage / 100);
        $totalCost = $baseCost + $overheadAmount;

        // Price = totalCost / (1 - margin%)
        $marginDecimal = $this->targetProfitMargin / 100;
        $recommendedPrice = $marginDecimal < 1 ? $totalCost / (1 - $marginDecimal) : $totalCost * 3;

        // Positioning multiplier
        $multiplier = match ($this->positioning) {
            'economy' => 0.85,
            'premium' => 1.25,
            default => 1.0,
        };
        $recommendedPrice *= $multiplier;

        // Round to .99 or .49
        $recommendedPrice = $this->roundPrice($recommendedPrice);

        $minPrice = $this->roundPrice($totalCost * 1.15); // 15% min margin
        $maxPrice = $this->roundPrice($totalCost / (1 - 0.70) * $multiplier); // 70% margin

        $currentPrice = null;
        if ($this->selectedProductId) {
            $product = Product::find($this->selectedProductId);
            $currentPrice = $product?->price ? (float) $product->price : null;
        }

        $this->result = [
            'ingredient_cost' => round($this->ingredientCost, 2),
            'labor_cost' => round($laborCost, 2),
            'overhead' => round($overheadAmount, 2),
            'total_cost' => round($totalCost, 2),
            'recommended_price' => $recommendedPrice,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'current_price' => $currentPrice,
            'profit_per_unit' => round($recommendedPrice - $totalCost, 2),
            'actual_margin' => $recommendedPrice > 0 ? round((($recommendedPrice - $totalCost) / $recommendedPrice) * 100, 1) : 0,
            'bulk' => [
                ['qty' => 6, 'label' => '6-pack', 'unit_price' => $this->roundPrice($recommendedPrice * 0.90), 'total' => round($recommendedPrice * 0.90 * 6, 2)],
                ['qty' => 12, 'label' => 'Dozen', 'unit_price' => $this->roundPrice($recommendedPrice * 0.85), 'total' => round($recommendedPrice * 0.85 * 12, 2)],
            ],
        ];
    }

    protected function roundPrice(float $price): float
    {
        if ($price < 5) {
            return round(ceil($price * 4) / 4 - 0.01, 2);
        }
        return round(floor($price) + 0.99, 2);
    }
}
