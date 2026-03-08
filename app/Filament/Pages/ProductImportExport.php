<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Services\ProductCsvService;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductImportExport extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected string $view = 'filament.pages.product-import-export';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $title = 'Import / Export';

    protected static ?string $navigationLabel = 'Import / Export';

    protected static ?int $navigationSort = 15;

    public ?array $data = [];

    public ?array $importResults = null;

    public ?array $previewData = null;

    public ?array $previewErrors = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form')
                ->schema($this->getFormSchema()),
        ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => Form::make($this, [
                EmbeddedSchema::make('form')
                    ->schema($this->getFormSchema()),
            ])->statePath('data'),
        ];
    }

    protected function getFormSchema(): array
    {
        $components = [
            Section::make('Export Products')
                ->description('Download all products as a CSV file.')
                ->schema([
                    Actions::make([
                        Action::make('exportCsv')
                            ->label('Export Products CSV')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->action(function () {
                                $service = new ProductCsvService;
                                $csv = $service->export();
                                $filename = 'products-'.now()->format('Y-m-d').'.csv';

                                return response()->streamDownload(function () use ($csv) {
                                    echo $csv;
                                }, $filename, ['Content-Type' => 'text/csv']);
                            }),
                        Action::make('downloadTemplate')
                            ->label('Download CSV Template')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('gray')
                            ->action(function () {
                                $service = new ProductCsvService;
                                $csv = $service->getTemplateContent();

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
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', '.csv'])
                        ->maxSize(5120)
                        ->directory('csv-imports')
                        ->visibility('private'),
                    Actions::make([
                        Action::make('previewImport')
                            ->label('Preview Import')
                            ->icon('heroicon-o-eye')
                            ->color('warning')
                            ->action(function () {
                                $filePath = $this->data['csv_file'] ?? null;

                                if (! $filePath) {
                                    Notification::make()->title('Please upload a CSV file first.')->danger()->send();

                                    return;
                                }

                                $fullPath = storage_path('app/private/'.$filePath);

                                if (! file_exists($fullPath)) {
                                    // Try public disk
                                    $fullPath = storage_path('app/public/'.$filePath);
                                }

                                if (! file_exists($fullPath)) {
                                    Notification::make()->title('File not found. Please re-upload.')->danger()->send();

                                    return;
                                }

                                $file = new \Illuminate\Http\UploadedFile($fullPath, basename($fullPath));
                                $service = new ProductCsvService;
                                $result = $service->parseForPreview($file);

                                $this->previewData = $result['rows'];
                                $this->previewErrors = $result['errors'];

                                if (empty($result['errors'])) {
                                    Notification::make()
                                        ->title('Preview ready: '.count($result['rows']).' rows found.')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Preview has '.count($result['errors']).' error(s). Fix before importing.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        Action::make('importCsv')
                            ->label('Import Products')
                            ->icon('heroicon-o-arrow-up-tray')
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

                                $fullPath = storage_path('app/private/'.$filePath);

                                if (! file_exists($fullPath)) {
                                    $fullPath = storage_path('app/public/'.$filePath);
                                }

                                if (! file_exists($fullPath)) {
                                    Notification::make()->title('File not found. Please re-upload.')->danger()->send();

                                    return;
                                }

                                $file = new \Illuminate\Http\UploadedFile($fullPath, basename($fullPath));
                                $service = new ProductCsvService;
                                $this->importResults = $service->import($file);

                                if (empty($this->importResults['errors'])) {
                                    Notification::make()
                                        ->title('Import complete!')
                                        ->body("{$this->importResults['created']} created, {$this->importResults['updated']} updated.")
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Import finished with errors')
                                        ->body("{$this->importResults['created']} created, {$this->importResults['updated']} updated, ".count($this->importResults['errors']).' errors.')
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
