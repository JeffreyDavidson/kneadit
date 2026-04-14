<?php

namespace App\Filament\Pages\Tools;

use App\Actions\Inventory\ImportProducts;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Export\ProductCsvExporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Laravel\Pennant\Feature;

class ProductImportExport extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.tools.product-import-export';

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $title = 'Import / Export';

    protected static ?string $navigationLabel = 'Import / Export';

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
        return $schema
            ->components($this->getFormSchema())
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make($this->getFormSchema()),
        ]);
    }

    /** @return array<int, Component> */
    protected function getFormSchema(): array
    {
        $components = [
            Section::make('Export Products')
                ->description('Download all products as a CSV file.')
                ->schema([
                    Actions::make([
                        Action::make('exportCsv')
                            ->label('Export Products CSV')
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->color('success')
                            ->action(function () {
                                $csv = resolve(ProductCsvExporter::class)->export();
                                $filename = 'products-' . now()->format('Y-m-d') . '.csv';

                                return response()->streamDownload(function () use ($csv) {
                                    echo $csv;
                                }, $filename, ['Content-Type' => 'text/csv']);
                            }),
                        Action::make('downloadTemplate')
                            ->label('Download CSV Template')
                            ->icon(Heroicon::OutlinedDocumentArrowDown)
                            ->color('gray')
                            ->action(function () {
                                $csv = resolve(ProductCsvExporter::class)->getTemplateContent();

                                return response()->streamDownload(function () use ($csv) {
                                    echo $csv;
                                }, 'products-template.csv', ['Content-Type' => 'text/csv']);
                            }),
                    ]),
                ]),

            Section::make('Import Products')
                ->description('Upload a CSV file to create or update products. Products are matched by name.')
                ->schema([
                    FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->maxSize(5120)
                        ->directory('csv-imports')
                        ->visibility('private'),
                    Actions::make([
                        Action::make('previewImport')
                            ->label('Preview Import')
                            ->icon(Heroicon::OutlinedEye)
                            ->color('warning')
                            ->action(function () {
                                $filePath = $this->data['csv_file'] ?? null;

                                if (! $filePath) {
                                    Notification::make()->title('Please upload a CSV file first.')->danger()->send();

                                    return;
                                }

                                $fullPath = storage_path('app/private/' . $filePath);

                                if (! file_exists($fullPath)) {
                                    // Try public disk
                                    $fullPath = storage_path('app/public/' . $filePath);
                                }

                                if (! file_exists($fullPath)) {
                                    Notification::make()->title('File not found. Please re-upload.')->danger()->send();

                                    return;
                                }

                                $file = new UploadedFile($fullPath, basename($fullPath));
                                $result = resolve(ProductCsvExporter::class)->parseForPreview($file);

                                $this->previewData = $result['rows'];
                                $this->previewErrors = $result['errors'];

                                if (empty($result['errors'])) {
                                    Notification::make()
                                        ->title('Preview ready: ' . count($result['rows']) . ' rows found.')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Preview has ' . count($result['errors']) . ' error(s). Fix before importing.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        Action::make('importCsv')
                            ->label('Import Products')
                            ->icon(Heroicon::OutlinedArrowUpTray)
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Confirm Import')
                            ->modalDescription('This will create new products and update existing ones matched by name. Continue?')
                            ->action(function () {
                                $filePath = $this->data['csv_file'] ?? null;

                                if (! $filePath) {
                                    Notification::make()->title('Please upload a CSV file first.')->danger()->send();

                                    return;
                                }

                                $fullPath = storage_path('app/private/' . $filePath);

                                if (! file_exists($fullPath)) {
                                    $fullPath = storage_path('app/public/' . $filePath);
                                }

                                if (! file_exists($fullPath)) {
                                    Notification::make()->title('File not found. Please re-upload.')->danger()->send();

                                    return;
                                }

                                $file = new UploadedFile($fullPath, basename($fullPath));
                                $this->importResults = resolve(ImportProducts::class)($file);

                                if (empty($this->importResults['errors'])) {
                                    Notification::make()
                                        ->title('Import complete!')
                                        ->body("{$this->importResults['created']} created, {$this->importResults['updated']} updated.")
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Import finished with errors')
                                        ->body("{$this->importResults['created']} created, {$this->importResults['updated']} updated, " . count($this->importResults['errors']) . ' errors.')
                                        ->warning()
                                        ->send();
                                }

                                $this->previewData = null;
                                $this->previewErrors = null;
                            }),
                    ]),
                ]),
        ];

        return $components;
    }

    protected function getViewData(): array
    {
        return [
            'importResults' => $this->importResults,
            'previewData' => $this->previewData,
            'previewErrors' => $this->previewErrors,
        ];
    }
}
