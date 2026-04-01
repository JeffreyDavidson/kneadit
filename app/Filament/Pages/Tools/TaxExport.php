<?php

namespace App\Filament\Pages\Tools;

use App\Enums\SubscriptionTier;
use App\Enums\TaxExportType;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Services\Financial\TaxCsvExporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxExport extends Page
{
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.tax-export';

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

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @return array<int, Component> */
    protected function getFormSchema(): array
    {
        $years = $this->getAvailableYears();

        return [
            Section::make('Tax Season Export')
                ->description('Generate CSV exports of your financial data for tax preparation.')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->schema([
                    Select::make('year')
                        ->label('Tax Year')
                        ->options($years)
                        ->required()
                        ->default(now()->year),

                    Select::make('export_type')
                        ->label('What to Export')
                        ->options(TaxExportType::class)
                        ->default('all')
                        ->required(),

                    DatePicker::make('date_from')
                        ->label('Date Range Override (From)')
                        ->placeholder('Leave blank for full year'),

                    DatePicker::make('date_to')
                        ->label('Date Range Override (To)')
                        ->placeholder('Leave blank for full year'),

                    Actions::make([
                        Action::make('exportCsv')
                            ->label('Download CSV Export')
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->color('primary')
                            ->action(function () {
                                $data = $this->form->getState();

                                return $this->generateExport($data);
                            }),
                    ]),
                ]),
        ];
    }

    /** @return array<int, string> */
    protected function getAvailableYears(): array
    {
        $orderYears = Order::query()->pluck('created_at')->map(fn ($d) => $d->year);
        $expenseYears = Expense::query()->pluck('date')->map(fn ($d) => $d->year);
        $incomeYears = Income::query()->pluck('date')->map(fn ($d) => $d->year);

        $allYears = $orderYears->merge($expenseYears)->merge($incomeYears)
            ->unique()->sort()->reverse()->values();

        if ($allYears->isEmpty()) {
            $allYears = collect([now()->year]);
        }

        return $allYears->mapWithKeys(fn (int $y) => [$y => (string) $y])->all();
    }

    /** @param array<string, mixed> $data */
    protected function generateExport(array $data): StreamedResponse
    {
        $year = (int) $data['year'];
        $type = $data['export_type'];
        $dateFrom = $data['date_from'] ?? "{$year}-01-01";
        $dateTo = $data['date_to'] ?? "{$year}-12-31";

        $filename = "tax-export-{$year}-{$type}.csv";

        return response()->streamDownload(function () use ($type, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            throw_if($handle === false, \RuntimeException::class, 'Failed to open file handle');

            $exporter = resolve(TaxCsvExporter::class);

            if (in_array($type, ['all', 'orders'])) {
                $exporter->writeOrdersCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'expenses'])) {
                $exporter->writeExpensesCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'income'])) {
                $exporter->writeIncomeCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'summary'])) {
                $exporter->writeSummaryCsv($handle, $dateFrom, $dateTo);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
