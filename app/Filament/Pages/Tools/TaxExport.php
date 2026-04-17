<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Financial\TaxExportType;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Pages\Tools\Schemas\TaxExportForm;
use App\Services\Financial\TaxCsvExporter;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read Schema $form
 */
class TaxExport extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.tools.tax-export';

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $title = 'Tax Export';

    protected static ?string $navigationLabel = 'Tax Export';

    protected static ?int $navigationSort = 4;

    public ?int $selectedYear = null;

    public ?string $exportType = 'all';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
        $this->form->fill([
            'year' => $this->selectedYear,
            'export_type' => 'all',
        ]);
    }

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return TaxExportForm::configure($schema)
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make(TaxExportForm::getComponents()),
        ]);
    }

    /** @param array<string, mixed> $data */
    protected function generateExport(array $data): StreamedResponse
    {
        $year = (int) $data['year'];
        $type = TaxExportType::from($data['export_type']);
        $dateFrom = $data['date_from'] ?? "{$year}-01-01";
        $dateTo = $data['date_to'] ?? "{$year}-12-31";

        $filename = "tax-export-{$year}-{$type->value}.csv";

        return response()->streamDownload(function () use ($type, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            throw_if($handle === false, \RuntimeException::class, 'Failed to open file handle');

            $exporter = resolve(TaxCsvExporter::class);

            if (in_array($type, [TaxExportType::All, TaxExportType::Orders])) {
                $exporter->writeOrdersCsv($handle, $dateFrom, $dateTo);
                if ($type === TaxExportType::All) {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, [TaxExportType::All, TaxExportType::Expenses])) {
                $exporter->writeExpensesCsv($handle, $dateFrom, $dateTo);
                if ($type === TaxExportType::All) {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, [TaxExportType::All, TaxExportType::Income])) {
                $exporter->writeIncomeCsv($handle, $dateFrom, $dateTo);
                if ($type === TaxExportType::All) {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, [TaxExportType::All, TaxExportType::Summary])) {
                $exporter->writeSummaryCsv($handle, $dateFrom, $dateTo);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
