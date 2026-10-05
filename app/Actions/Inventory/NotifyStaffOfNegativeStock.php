<?php

namespace App\Actions\Inventory;

use App\DataTransferObjects\Inventory\IngredientShortfall;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Support\StockQuantity;
use Filament\Notifications\Notification;

/**
 * Tells every staff member which ingredients a bake left below zero. Starting to
 * bake always goes through; this is how the shortfall gets noticed and reordered.
 */
class NotifyStaffOfNegativeStock
{
    /**
     * @param  array<int, IngredientShortfall>  $shortfalls
     */
    public function __invoke(Order $order, array $shortfalls): void
    {
        if ($shortfalls === []) {
            return;
        }

        $body = collect($shortfalls)
            ->map(fn (IngredientShortfall $shortfall): string => "{$shortfall->ingredient->name} is now ".StockQuantity::display($shortfall->shortfall)." {$shortfall->ingredient->unit} below zero.")
            ->implode(' ');

        Notification::make()
            ->title("Order {$order->order_number} is baking without enough stock")
            ->body("{$body} Check your ingredient counts and reorder.")
            ->warning()
            ->sendToDatabase(User::query()->get(), isEventDispatched: true);
    }
}
