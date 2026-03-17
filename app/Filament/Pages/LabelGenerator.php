<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LabelGenerator extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Labels';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.label-generator';

    public array $selectedProducts = [];

    public string $labelSize = 'medium';

    public int $quantity = 1;

    public bool $includeQrCode = false;

    public bool $includeAllergyDisclaimer = true;

    public bool $includeBarcode = false;

    public ?string $bestByDate = null;

    public bool $showPreview = false;

    public function mount(): void
    {
        $shelfLifeDays = (int) Setting::get('default_shelf_life_days', '3');
        $this->bestByDate = now()->addDays($shelfLifeDays)->format('Y-m-d');
    }

    public function generateLabels(): void
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('notify', type: 'warning', message: 'Please select at least one product.');

            return;
        }

        $this->showPreview = true;
    }

    public function getProducts(): Collection
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price]);
    }

    public function getSelectedProductModels(): Collection
    {
        return Product::whereIn('id', $this->selectedProducts)->with('recipe')->get();
    }

    public function getLabelDimensions(): array
    {
        return match ($this->labelSize) {
            'small' => ['width' => '2in', 'height' => '1in', 'cols' => 4],
            'medium' => ['width' => '3in', 'height' => '2in', 'cols' => 3],
            'large' => ['width' => '4in', 'height' => '3in', 'cols' => 2],
            default => ['width' => '3in', 'height' => '2in', 'cols' => 3],
        };
    }

    public function getStoreName(): string
    {
        return Setting::get('store_name', 'Our Bakery');
    }

    public function getAllergyDisclaimer(): string
    {
        return Setting::get('allergy_disclaimer', 'May contain allergens.');
    }
}
