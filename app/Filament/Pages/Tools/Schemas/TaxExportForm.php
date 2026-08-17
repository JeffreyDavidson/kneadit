<?php

namespace App\Filament\Pages\Tools\Schemas;

use App\Enums\Financial\TaxExportType;
use App\Filament\Pages\Tools\TaxExport;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TaxExportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::getComponents());
    }

    /** @return array<int, Component> */
    public static function getComponents(): array
    {
        $years = static::getAvailableYears();

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
                            ->action(function (TaxExport $livewire) {
                                $data = $livewire->form->getState();

                                return $livewire->generateExport($data);
                            }),
                    ]),
                ]),
        ];
    }

    /** @return array<int, string> */
    protected static function getAvailableYears(): array
    {
        $orderYears = Order::query()->get(['created_at'])
            ->flatMap(fn (Order $order): array => $order->created_at === null ? [] : [$order->created_at->year]);
        $expenseYears = Expense::query()->get(['date'])
            ->flatMap(fn (Expense $expense): array => $expense->date === null ? [] : [$expense->date->year]);
        $incomeYears = Income::query()->get(['date'])
            ->flatMap(fn (Income $income): array => $income->date === null ? [] : [$income->date->year]);

        $allYears = $orderYears->merge($expenseYears)->merge($incomeYears)
            ->unique()->sort()->reverse()->values();

        if ($allYears->isEmpty()) {
            $allYears = collect([now()->year]);
        }

        return $allYears->mapWithKeys(fn (int $y) => [$y => (string) $y])->all();
    }
}
