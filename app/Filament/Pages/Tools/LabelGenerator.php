<?php

namespace App\Filament\Pages\Tools;

use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Inventory\Product;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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

    /** @var array<int, int> */
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
        $storedShelfLife = resolve(SettingsManager::class)->get('default_shelf_life_days', '3');
        $shelfLifeDays = filter_var($storedShelfLife, FILTER_VALIDATE_INT);
        $shelfLifeDays = is_int($shelfLifeDays) ? $shelfLifeDays : 3;
        $this->bestByDate = now()->addDays($shelfLifeDays)->format('Y-m-d');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Label Settings')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->schema([
                    Select::make('selectedProducts')
                        ->label('Products')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options($this->getProductOptions())
                        ->live()
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        Select::make('labelSize')
                            ->label('Label Size')
                            ->options([
                                'small' => 'Small (2" × 1")',
                                'medium' => 'Medium (3" × 2")',
                                'large' => 'Large (4" × 3")',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('quantity')
                            ->label('Qty per Product')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->live(),

                        DatePicker::make('bestByDate')
                            ->label('Best By Date')
                            ->required()
                            ->live(),
                    ]),

                    Grid::make(3)->schema([
                        Checkbox::make('includeQrCode')
                            ->label('Include QR Code')
                            ->live(),
                        Checkbox::make('includeAllergyDisclaimer')
                            ->label('Include Allergy Disclaimer')
                            ->live(),
                        Checkbox::make('includeBarcode')
                            ->label('Include Barcode')
                            ->live(),
                    ]),
                ]),
        ]);
    }

    public function generateLabels(): void
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('notify', type: 'warning', message: 'Please select at least one product.');

            return;
        }

        $this->showPreview = true;
    }

    /** @return array<int, string> */
    public function getProductOptions(): array
    {
        return Product::query()->active()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Product $p): array => [
                $p->id => $p->name . ' — ' . ($p->price instanceof Money ? $p->price->formatted() : '$' . number_format((float) $p->price, 2)),
            ])
            ->all();
    }

    /** @return Collection<int, Product> */
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
        return resolve(TenantSettings::class)->store->name;
    }

    public function getAllergyDisclaimer(): string
    {
        return resolve(TenantSettings::class)->branding->allergyDisclaimer ?? 'May contain allergens.';
    }
}
