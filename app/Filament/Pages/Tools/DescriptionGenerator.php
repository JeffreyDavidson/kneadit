<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Product;
use App\Services\Content\DescriptionGeneratorService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class DescriptionGenerator extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Descriptions';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.tools.description-generator';

    public ?string $selectedProductId = null;

    public string $manualProductName = '';

    public string $tone = 'professional';

    public string $length = 'medium';

    /** @var array<int, string> */
    public array $descriptions = [];

    /** @return Collection<int, mixed> */
    public function getProductsProperty(): Collection
    {
        return Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function generate(): void
    {
        $service = resolve(DescriptionGeneratorService::class);

        $product = null;
        $productName = $this->manualProductName;
        $category = null;
        $price = null;

        if ($this->selectedProductId) {
            $product = Product::with('category')->find($this->selectedProductId);
            if ($product) {
                $productName = $product->name;
                $category = $product->category?->name;
                $price = $product->price?->dollars();
            }
        }

        if (empty($productName)) {
            $this->descriptions = [];

            return;
        }

        $this->descriptions = $service->generate($productName, $this->tone, $this->length, $category, $price);
    }

    public function applyToProduct(int $index): void
    {
        if (! $this->selectedProductId || ! isset($this->descriptions[$index])) {
            return;
        }

        $product = Product::query()->find($this->selectedProductId);
        if ($product) {
            $product->update(['description' => $this->descriptions[$index]]);
            Notification::make()
                ->title('Description applied!')
                ->body("Description saved to {$product->name}.")
                ->success()
                ->send();
        }
    }
}
