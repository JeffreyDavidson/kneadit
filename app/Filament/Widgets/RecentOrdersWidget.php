<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        $url = route('filament.admin.resources.orders.index');

        return new \Illuminate\Support\HtmlString(
            '<div style="display:flex;justify-content:space-between;align-items:center;width:100%;">'
            .'<span>Recent Orders</span>'
            .'<a href="'.$url.'" style="color:#FFFFFF;font-size:0.85rem;font-weight:500;opacity:0.85;text-decoration:none;">View All →</a>'
            .'</div>'
        );
    }

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
            ->paginated(false);
    }
}
