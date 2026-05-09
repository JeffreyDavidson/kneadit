<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Orders\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Support\Enums\FontWeight;

class RecentOrdersWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with('customer')->latest()->limit(10))
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record->id]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight(FontWeight::Bold),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('total')
                    ->money('USD'),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since(),
            ])
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(OrderResource::getUrl('index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }
}
