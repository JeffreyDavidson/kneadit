<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Pages\Tools\Schemas\ProductImportExportForm;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;

/**
 * @property-read Schema $form
 */
class ProductImportExport extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    protected string $view = 'filament.pages.tools.product-import-export';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?string $title = 'Import / Export';

    #[\Override]
    protected static ?string $navigationLabel = 'Import / Export';

    #[\Override]
    protected static ?int $navigationSort = 15;

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<string, mixed> */
    public ?array $importResults = null;

    /** @var array<int, array<string, mixed>>|null */
    public ?array $previewData = null;

    /** @var array<int, string>|null */
    public ?array $previewErrors = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return ProductImportExportForm::configure($schema)
            ->statePath('data');
    }

    #[\Override]
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make(ProductImportExportForm::getComponents()),
        ]);
    }

    #[\Override]
    protected function getViewData(): array
    {
        return [
            'importResults' => $this->importResults,
            'previewData' => $this->previewData,
            'previewErrors' => $this->previewErrors,
        ];
    }
}
