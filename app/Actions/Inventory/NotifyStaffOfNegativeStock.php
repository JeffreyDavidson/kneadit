<?php

namespace App\Actions\Inventory;

use App\DataTransferObjects\Inventory\IngredientShortfall;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Support\StockQuantity;
use Filament\Notifications\Notification;

/**
 * Tells staff which ingredients a bake left below zero: a toast for whoever pressed
 * Start Baking and a bell notification for every staff member. Starting to bake always
 * goes through; this is how the shortfall gets noticed and reordered.
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

        $notification = Notification::make()
            ->title("Order {$order->order_number} is baking without enough stock")
            ->body("{$body} Check your ingredient counts and reorder.")
            ->warning();

        // A toast for the person who just pressed Start Baking, and a bell notification for everyone.
        $notification->send();
        $notification->sendToDatabase(User::query()->get(), isEventDispatched: true);
    }
}
