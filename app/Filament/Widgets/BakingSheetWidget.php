<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BakingSheetWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Daily Baking Sheet';

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey(Model|array $record): string
    {
        return (string) $record->product_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->selectRaw('order_items.product_id, products.name as product_name, SUM(order_items.quantity) as total_quantity')
                    ->whereHas('order', function (Builder $query) {
                        $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                            ->where(function (Builder $q) {
                                $q->whereDate('delivery_date', Carbon::today())
                                    ->orWhere(function (Builder $q2) {
                                        $q2->whereDate('delivery_date', '>', Carbon::today())
                                            ->where('status', OrderStatus::Confirmed);
                                    });
                            });
                    })
                    ->groupBy('order_items.product_id', 'products.name')
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Qty Needed')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('total_quantity', 'desc')
            ->emptyStateHeading('Nothing to bake!')
            ->emptyStateIcon('heroicon-o-cake')
            ->paginated(false);
    }
}
