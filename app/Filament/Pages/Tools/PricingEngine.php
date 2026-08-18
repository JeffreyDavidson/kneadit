<?php

namespace App\Filament\Pages\Tools;

use App\DataTransferObjects\Financial\PricingRecommendation;
use App\Enums\Financial\PricingPosition;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Product;
use App\Models\Platform\Setting;
use App\Services\Financial\PricingRecommendationService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class PricingEngine extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Pricing';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.tools.pricing-engine';

    public ?string $selectedProductId = null;

    public float $ingredientCost = 0;

    public int $prepTimeMinutes = 0;

    public float $hourlyLaborRate = 15;

    public float $overheadPercentage = 20;

    public int $targetProfitMargin = 50;

    public string $positioning = 'standard';

    public ?PricingRecommendation $result = null;

    public function mount(): void
    {
        $this->hourlyLaborRate = (float) (Setting::query()->where('key', 'hourly_labor_rate')->value('value') ?? 15);
        $this->overheadPercentage = (float) (Setting::query()->where('key', 'overhead_percentage')->value('value') ?? 20);
        $this->targetProfitMargin = (int) (Setting::query()->where('key', 'target_profit_margin')->value('value') ?? 50);
    }

    /** @return Collection<int, mixed> */
    public function getProductsProperty(): Collection
    {
        return Product::query()->with(['category', 'recipes'])->orderBy('name')->get();
    }

    public function updatedSelectedProductId(): void
    {
        if (! $this->selectedProductId) {
            $this->ingredientCost = 0;
            $this->prepTimeMinutes = 0;

            return;
        }

        $product = Product::with('recipes')->find($this->selectedProductId);
        if (! $product) {
            return;
        }

        // Pull cost from recipe if available
        $recipe = $product->recipes->first();
        if ($recipe) {
            $this->ingredientCost = $recipe->cost?->dollars() ?? $product->cost?->dollars() ?? 0.0;
            $this->prepTimeMinutes = (int) ($recipe->prep_time_minutes ?? 0);
        } else {
            $this->ingredientCost = $product->cost?->dollars() ?? 0.0;
            $this->prepTimeMinutes = 0;
        }
    }

    public function calculate(): void
    {
        $currentPrice = null;
        if ($this->selectedProductId) {
            $product = Product::query()->find($this->selectedProductId);
            $currentPrice = $product?->price?->dollars();
        }

        $this->result = resolve(PricingRecommendationService::class)->recommend(
            ingredientCost: $this->ingredientCost,
            prepTimeMinutes: $this->prepTimeMinutes,
            hourlyLaborRate: $this->hourlyLaborRate,
            overheadPercentage: $this->overheadPercentage,
            targetMarginPercent: $this->targetProfitMargin,
            positioning: PricingPosition::from($this->positioning),
            currentPrice: $currentPrice,
        );
    }
}
