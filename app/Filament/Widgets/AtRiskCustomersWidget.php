<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use App\Queries\Customers\AtRiskCustomersQuery;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AtRiskCustomersWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 10;

    protected static ?string $heading = 'At Risk Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AtRiskCustomersQuery::query(
                    (int) config('analytics.at_risk_threshold_days', 30),
                )->limit($this->rowLimit()),
            )
            ->columns($this->columnSet())
            ->paginated(false)
            ->defaultSort('last_order_date', 'asc')
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.customers.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            WidgetSize::Large => 10,
        };
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        // at_risk_customers is constrained to md/lg in WidgetMeta — names + days
        // inactive are the actionable columns at md; lifetime value adds context
        // at lg.
        $columns = [
            TextColumn::make('name')
                ->label('Customer')
                ->url(fn (Customer $record): string => route('filament.admin.resources.customers.edit', $record)),

            TextColumn::make('last_order_date')
                ->label('Last Order')
                ->since(),

            TextColumn::make('days_since_last_order')
                ->label('Days Inactive')
                ->suffix(' days'),
        ];

        if ($this->isSize('lg')) {
            $columns[] = TextColumn::make('lifetime_value')
                ->label('Lifetime Value')
                ->money('USD');
        }

        return $columns;
    }
}
