<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Query\Builder;

class AtRiskCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $heading = 'At Risk Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->whereHas('orders', function (Builder $query) {
                        $query->whereNotIn('status', [OrderStatus::Cancelled]);
                    })
                    ->whereDoesntHave('orders', function (Builder $query) {
                        $query->whereNotIn('status', [OrderStatus::Cancelled])
                            ->where('created_at', '>=', now()->subDays(30));
                    })
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Customer')
                    ->url(fn (Customer $record): string => route('filament.admin.resources.customers.edit', $record)),

                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->since(),

                TextColumn::make('lifetime_value')
                    ->label('Lifetime Value')
                    ->money('USD'),

                TextColumn::make('days_since_last_order')
                    ->label('Days Inactive')
                    ->suffix(' days'),
            ])
            ->paginated(false)
            ->defaultSort('last_order_date', 'asc');
    }
}
