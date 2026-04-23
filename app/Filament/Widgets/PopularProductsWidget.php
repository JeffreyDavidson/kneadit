<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Orders\OrderItem;
use App\ValueObjects\DateRange;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PopularProductsWidget extends BaseWidget
{
    use CachesWidgetData;

    protected static ?int $sort = 4;

    protected static ?string $heading = 'Popular Products This Week';

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey(Model|array $record): string
    {
        return (string) (is_array($record) ? $record['product_id'] : $record->getAttribute('product_id'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => $this->fetchTopProducts())
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('total_qty')
                    ->label('Qty')
                    ->badge(),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('usd'),
            ])
            ->emptyStateHeading('No orders this week yet')
            ->emptyStateIcon(Heroicon::OutlinedCake);
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchTopProducts(): array
    {
        // Cache plain arrays, not Eloquent collections. Cache stores hydrate as
        // __PHP_Incomplete_Class because config(cache.serializable_classes) is false.
        // Same shape as #302.
        return $this->cached('main', [900, 1800], function (): array {
            $range = DateRange::thisWeek();

            return OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.status', '!=', OrderStatus::Cancelled)
                ->whereBetween('orders.delivery_date', $range->toArray())
                ->select(
                    'order_items.product_id',
                    'products.name as product_name',
                    DB::raw('SUM(order_items.quantity) as total_qty'),
                    DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue'),
                )
                ->groupBy('order_items.product_id', 'products.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get()
                ->toArray();
        });
    }

    protected function cachePrefix(): string
    {
        return 'popular_products';
    }
}
