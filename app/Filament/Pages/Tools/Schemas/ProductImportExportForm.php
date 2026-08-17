<?php

namespace App\Filament\Pages\Tools\Schemas;

use App\Actions\Inventory\ImportProducts;
use App\Filament\Pages\Tools\ProductImportExport as Livewire;
use App\Services\Export\ProductCsvExporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;

class ProductImportExportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::getComponents());
    }

    /** @return array<int, Component> */
    public static function getComponents(): array
    {
        return [
            static::exportSection(),
            static::importSection(),
        ];
    }

    protected static function exportSection(): Section
    {
        return Section::make('Export Products')
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
            ]);
    }

    protected static function importSection(): Section
    {
        return Section::make('Import Products')
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
                        ->action(function (Livewire $livewire) {
                            $filePath = $livewire->data['csv_file'] ?? null;

                            if (! is_string($filePath) || $filePath === '') {
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

                            $livewire->previewData = $result['rows'];
                            $livewire->previewErrors = $result['errors'];

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
                        ->action(function (Livewire $livewire) {
                            $filePath = $livewire->data['csv_file'] ?? null;

                            if (! is_string($filePath) || $filePath === '') {
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
                            $livewire->importResults = resolve(ImportProducts::class)($file);

                            if (empty($livewire->importResults['errors'])) {
                                Notification::make()
                                    ->title('Import complete!')
                                    ->body("{$livewire->importResults['created']} created, {$livewire->importResults['updated']} updated.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Import finished with errors')
                                    ->body("{$livewire->importResults['created']} created, {$livewire->importResults['updated']} updated, " . count($livewire->importResults['errors']) . ' errors.')
                                    ->warning()
                                    ->send();
                            }

                            $livewire->previewData = null;
                            $livewire->previewErrors = null;
                        }),
                ]),
            ]);
    }
}
