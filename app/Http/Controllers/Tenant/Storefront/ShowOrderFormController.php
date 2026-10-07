<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Carts\CartManager;
use App\Services\Orders\OrderAccessGuard;
use App\Services\Orders\OrderLineRefresher;
use App\Services\Scheduling\EarliestDeliveryDate;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowOrderFormController extends Controller
{
    public function __invoke(
        Request $request,
        TenantSettings $settings,
        CartManager $cartManager,
        EarliestDeliveryDate $earliestDeliveryDate,
        OrderLineRefresher $refresher,
    ): View {
        $categories = Category::query()
            ->active()
            ->withActiveProducts()
            ->orderBy('sort_order')
            ->get();

        $cart = $cartManager->current();
        $hydratedItems = [];
        $removedItemNames = [];

        if ($cart instanceof Cart) {
            $refreshed = $refresher->refresh($cart->items()->with('product')->get());

            $hydratedItems = array_map(fn (array $line): array => [
                'id' => $line['product_id'],
                'name' => $line['name'],
                'price' => $line['price'],
                'quantity' => $line['quantity'],
            ], $refreshed->items);
            $removedItemNames = $refreshed->removedNames;
        }

        // The order form's script swaps the cart for a reordered order's items, so the
        // notice has to describe that order instead. Only visitors who may see it.
        $reorderedOrder = $request->filled('reorder')
            ? Order::query()->where('order_number', $request->string('reorder')->toString())->first()
            : null;

        if ($reorderedOrder instanceof Order && OrderAccessGuard::canAccess($reorderedOrder)) {
            $removedItemNames = $refresher->refresh($reorderedOrder->orderItems()->with('product')->get())->removedNames;
        }

        return view('tenant.storefront.order', [
            'settings' => $settings,
            'categories' => $categories,
            'content' => settingsPageContent('order'),
            'storefrontTheme' => $settings->branding->storefrontTheme,
            'hydratedCartItems' => $hydratedItems,
            'removedItemNames' => $removedItemNames,
            'maxQuantity' => OrderItem::MAX_QUANTITY,
            'quantityLimitMessage' => __('orders.max_quantity', [
                'max' => OrderItem::MAX_QUANTITY,
            ]),
            'hydratedCartEmail' => $cart?->customer_email,
            'hydratedCartName' => $cart?->customer_name,
            'earliestDeliveryDate' => $earliestDeliveryDate->get(),
        ]);
    }
}
