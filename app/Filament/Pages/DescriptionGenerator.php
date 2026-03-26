<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Product;
use App\Services\DescriptionGeneratorService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class DescriptionGenerator extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Descriptions';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.description-generator';

    public ?string $selectedProductId = null;

    public string $manualProductName = '';

    public string $tone = 'professional';

    public string $length = 'medium';

    /** @var array<int, string> */
    public array $descriptions = [];

    /** @return Collection<int, Product> */
    public function getProductsProperty(): Collection
    {
        return Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function generate(): void
    {
        $service = new DescriptionGeneratorService;

        $product = null;
        $productName = $this->manualProductName;
        $category = null;
        $price = null;

        if ($this->selectedProductId) {
            $product = Product::with('category')->find($this->selectedProductId);
            if ($product) {
                $productName = $product->name;
                $category = $product->category?->name;
                $price = $product->price ? (float) $product->price : null;
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
