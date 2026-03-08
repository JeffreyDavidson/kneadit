<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AtRiskCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'At Risk Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->whereHas('orders', function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->whereDoesntHave('orders', function ($query) {
                        $query->where('status', '!=', 'cancelled')
                            ->where('created_at', '>=', now()->subDays(30));
                    })
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
            ->defaultSort('last_order_date', 'asc')
            ->limit(5);
    }
}
