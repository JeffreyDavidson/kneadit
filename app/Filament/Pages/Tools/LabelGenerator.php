<?php

namespace App\Filament\Pages\Tools;

use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Inventory\Product;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class LabelGenerator extends Page
{
    use RequiresManagerRole;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Labels';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.tools.label-generator';

    /** @var array<string, mixed> */
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
        $shelfLifeDays = (int) app(SettingsManager::class)->get('default_shelf_life_days', '3');
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

    /** @return Collection<int, mixed> */
    public function getProducts(): Collection
    {
        return Product::query()->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price]);
    }

    /** @return Collection<int, mixed> */
    public function getSelectedProductModels(): Collection
    {
        return Product::query()->whereIn('id', $this->selectedProducts)->with('recipe')->get();
    }

    /** @return array<string, mixed> */
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
        return app(TenantSettings::class)->storeName;
    }

    public function getAllergyDisclaimer(): string
    {
        return app(TenantSettings::class)->allergyDisclaimer ?? 'May contain allergens.';
    }
}
