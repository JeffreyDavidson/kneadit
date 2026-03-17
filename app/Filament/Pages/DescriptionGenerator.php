<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Product;
use App\Services\DescriptionGeneratorService;
use App\Traits\HasPlanGating;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class DescriptionGenerator extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Descriptions';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.description-generator';

    public ?string $selectedProductId = null;

    public string $manualProductName = '';

    public string $tone = 'professional';

    public string $length = 'medium';

    public array $descriptions = [];

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

        $product = Product::find($this->selectedProductId);
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
