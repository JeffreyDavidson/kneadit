<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\OrderItem;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class BakingSheetWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Daily Baking Sheet';

    public function getTableRecordKey(Model|array $record): string
    {
        return (string) (is_array($record) ? $record['product_id'] : $record->getAttribute('product_id'));
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
                                $q->whereDate('delivery_date', Date::today())
                                    ->orWhere(function (Builder $q2) {
                                        $q2->whereDate('delivery_date', '>', Date::today())
                                            ->where('status', OrderStatus::Confirmed);
                                    });
                            });
                    })
                    ->groupBy('order_items.product_id', 'products.name'),
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
            ->emptyStateIcon(Heroicon::OutlinedCake)
            ->paginated(false);
    }
}
