<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Recent Orders';

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.edit', $record))
                    ->color('primary'),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->limit(18),
                TextColumn::make('total')
                    ->money('usd'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'baking' => 'primary',
                        'ready' => 'success',
                        'delivered' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('When')
                    ->since(),
            ])
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View All →')
                    ->url(route('filament.admin.resources.orders.index'))
                    ->color('gray'),
            ]);
    }
}
