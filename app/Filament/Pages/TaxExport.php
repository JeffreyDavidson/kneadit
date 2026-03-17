<?php

namespace App\Filament\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Traits\RequiresRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxExport extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected string $view = 'filament.pages.tax-export';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';

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

    public ?array $data = [];

    protected function getFormSchema(): array
    {
        $years = $this->getAvailableYears();

        return [
            Section::make('Tax Season Export')
                ->description('Generate CSV exports of your financial data for tax preparation.')
                ->icon('heroicon-o-document-arrow-down')
                ->schema([
                    Select::make('year')
                        ->label('Tax Year')
                        ->options($years)
                        ->required()
                        ->default(now()->year),

                    Select::make('export_type')
                        ->label('What to Export')
                        ->options([
                            'all' => 'All (Orders + Expenses + Income + Summary)',
                            'orders' => 'Orders Only',
                            'expenses' => 'Expenses Only',
                            'income' => 'Income Only',
                            'summary' => 'Summary Only',
                        ])
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
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->action(function () {
                                $data = $this->form->getState();

                                return $this->generateExport($data);
                            }),
                    ]),
                ]),
        ];
    }

    protected function getAvailableYears(): array
    {
        $years = collect();

        $orderYears = Order::selectRaw('DISTINCT YEAR(created_at) as year')->pluck('year');
        $expenseYears = Expense::selectRaw('DISTINCT YEAR(date) as year')->pluck('year');
        $incomeYears = Income::selectRaw('DISTINCT YEAR(date) as year')->pluck('year');

        $allYears = $years->merge($orderYears)->merge($expenseYears)->merge($incomeYears)
            ->unique()->sort()->reverse()->values();

        if ($allYears->isEmpty()) {
            $allYears = collect([now()->year]);
        }

        return $allYears->mapWithKeys(fn ($y) => [$y => (string) $y])->toArray();
    }

    protected function generateExport(array $data): StreamedResponse
    {
        $year = (int) $data['year'];
        $type = $data['export_type'];
        $dateFrom = $data['date_from'] ?? "{$year}-01-01";
        $dateTo = $data['date_to'] ?? "{$year}-12-31";

        $filename = "tax-export-{$year}-{$type}.csv";

        return response()->streamDownload(function () use ($type, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');

            if (in_array($type, ['all', 'orders'])) {
                $this->writeOrdersCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'expenses'])) {
                $this->writeExpensesCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'income'])) {
                $this->writeIncomeCsv($handle, $dateFrom, $dateTo);
                if ($type === 'all') {
                    fputcsv($handle, []);
                }
            }

            if (in_array($type, ['all', 'summary'])) {
                $this->writeSummaryCsv($handle, $dateFrom, $dateTo);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function writeOrdersCsv($handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== ORDERS ===']);
        fputcsv($handle, ['Date', 'Order Number', 'Customer', 'Items', 'Subtotal', 'Delivery Fee', 'Discount', 'Total', 'Payment Status', 'Payment Method']);

        Order::with(['customer', 'orderItems.product'])
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->orderBy('created_at')
            ->chunk(100, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $items = $order->orderItems->map(fn ($i) => ($i->product->name ?? 'Item').' x'.$i->quantity)->implode('; ');
                    fputcsv($handle, [
                        $order->created_at->format('Y-m-d'),
                        $order->order_number,
                        $order->customer->name ?? 'N/A',
                        $items,
                        $order->subtotal,
                        $order->delivery_fee,
                        $order->discount_amount,
                        $order->total,
                        $order->payment_status,
                        $order->payment_method,
                    ]);
                }
            });
    }

    protected function writeExpensesCsv($handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== EXPENSES ===']);
        fputcsv($handle, ['Date', 'Category (IRS Schedule C)', 'Description', 'Amount', 'Business Use %', 'Deductible Amount', 'Vendor']);

        $categoryMap = [
            'ingredients' => 'Cost of Goods Sold (Line 4)',
            'supplies' => 'Supplies (Line 22)',
            'packaging' => 'Supplies (Line 22)',
            'booth_fees' => 'Other Expenses (Line 27a)',
            'delivery' => 'Car & Truck Expenses (Line 9)',
            'marketing' => 'Advertising (Line 8)',
            'insurance' => 'Insurance (Line 15)',
            'education' => 'Other Expenses (Line 27a)',
            'equipment' => 'Depreciation (Line 13)',
            'other' => 'Other Expenses (Line 27a)',
        ];

        Expense::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->chunk(100, function ($expenses) use ($handle, $categoryMap) {
                foreach ($expenses as $expense) {
                    fputcsv($handle, [
                        $expense->date->format('Y-m-d'),
                        $categoryMap[$expense->category] ?? 'Other Expenses (Line 27a)',
                        $expense->description,
                        $expense->amount,
                        $expense->business_percentage,
                        $expense->deductible_amount,
                        $expense->notes ?? '',
                    ]);
                }
            });
    }

    protected function writeIncomeCsv($handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== INCOME ===']);
        fputcsv($handle, ['Date', 'Source', 'Description', 'Amount', 'Category']);

        Income::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->chunk(100, function ($incomes) use ($handle) {
                foreach ($incomes as $income) {
                    fputcsv($handle, [
                        $income->date->format('Y-m-d'),
                        $income->source_label,
                        $income->description,
                        $income->amount,
                        'Gross Receipts (Schedule C Line 1)',
                    ]);
                }
            });
    }

    protected function writeSummaryCsv($handle, string $from, string $to): void
    {
        $totalOrderRevenue = Order::whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        $totalIncomeRevenue = Income::whereBetween('date', [$from, $to])->sum('amount');
        $totalRevenue = (float) $totalOrderRevenue + (float) $totalIncomeRevenue;

        $totalExpenses = (float) Expense::whereBetween('date', [$from, $to])->sum('amount');
        $totalDeductible = (float) Expense::whereBetween('date', [$from, $to])->sum('deductible_amount');
        $netProfit = $totalRevenue - $totalDeductible;

        fputcsv($handle, ['=== TAX SUMMARY ===']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Revenue (Orders)', number_format($totalOrderRevenue, 2)]);
        fputcsv($handle, ['Total Revenue (Other Income)', number_format($totalIncomeRevenue, 2)]);
        fputcsv($handle, ['Total Revenue (Combined)', number_format($totalRevenue, 2)]);
        fputcsv($handle, ['Total Expenses', number_format($totalExpenses, 2)]);
        fputcsv($handle, ['Total Deductible', number_format($totalDeductible, 2)]);
        fputcsv($handle, ['Net Profit (Revenue - Deductible)', number_format($netProfit, 2)]);
    }
}
