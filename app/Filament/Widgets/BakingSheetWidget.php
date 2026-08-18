<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\OrderItem;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class BakingSheetWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.baking-sheet';

    /**
     * Hide when there's nothing to bake — empty "Nothing to bake!"
     * tile on a busy ops dashboard is just dead space. Reappears
     * the moment any pending/confirmed/baking order item exists for
     * today (or a confirmed order item ahead of today).
     */
    public static function canView(): bool
    {
        return OrderItem::query()
            ->whereHas('order', function (Builder $query): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                    ->where(function (Builder $q): void {
                        $q->whereDate('delivery_date', Date::today())
                            ->orWhere(function (Builder $q2): void {
                                $q2->whereDate('delivery_date', '>', Date::today())
                                    ->where('status', OrderStatus::Confirmed);
                            });
                    });
            })
            ->exists();
    }

    /** @return array<int, array{product_id: int, name: string, quantity: int}> */
    public function getRows(): array
    {
        return OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('order_items.product_id, products.name as product_name, SUM(order_items.quantity) as total_quantity')
            ->whereHas('order', function (Builder $query): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                    ->where(function (Builder $q): void {
                        $q->whereDate('delivery_date', Date::today())
                            ->orWhere(function (Builder $q2): void {
                                $q2->whereDate('delivery_date', '>', Date::today())
                                    ->where('status', OrderStatus::Confirmed);
                            });
                    });
            })
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(fn (OrderItem $item): array => [
                'product_id' => (int) $item->product_id,
                'name' => is_string($item->getAttribute('product_name')) ? $item->getAttribute('product_name') : 'Unknown Product',
                'quantity' => Arr::integer($item->getAttributes(), 'total_quantity', 0),
            ])
            ->all();
    }
}
