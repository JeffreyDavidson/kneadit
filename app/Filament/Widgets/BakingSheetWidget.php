<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\OrderItem;
use App\Services\Scheduling\BakeryClock;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class BakingSheetWidget extends Widget
{
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected string $view = 'filament.widgets.baking-sheet';

    /**
     * Hide when there's nothing to bake — empty "Nothing to bake!"
     * tile on a busy ops dashboard is just dead space. Reappears
     * the moment any pending/confirmed/baking order item exists for
     * today (or a confirmed order item ahead of today).
     */
    #[\Override]
    public static function canView(): bool
    {
        $today = resolve(BakeryClock::class)->today();

        return OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($today): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                    ->where(function (Builder $q) use ($today): void {
                        $q->whereDate('delivery_date', $today)
                            ->orWhere(function (Builder $q2) use ($today): void {
                                $q2->whereDate('delivery_date', '>', $today)
                                    ->where('status', OrderStatus::Confirmed);
                            });
                    });
            })
            ->exists();
    }

    /** @return array<int, array{product_id: int, name: string, quantity: int}> */
    public function getRows(): array
    {
        $today = resolve(BakeryClock::class)->today();

        return OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('order_items.product_id, products.name as product_name, SUM(order_items.quantity) as total_quantity')
            ->whereHas('order', function (Builder $query) use ($today): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                    ->where(function (Builder $q) use ($today): void {
                        $q->whereDate('delivery_date', $today)
                            ->orWhere(function (Builder $q2) use ($today): void {
                                $q2->whereDate('delivery_date', '>', $today)
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
