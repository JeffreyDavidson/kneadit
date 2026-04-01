<?php

namespace App\Filament\Pages\Tools;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Ingredient;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

class ShoppingListGenerator extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Shopping List';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.shopping-list-generator';

    public string $startDate = '';

    public string $endDate = '';

    /** @var Collection<int, mixed> */
    public Collection $shoppingList;

    /** @var array<string, mixed> */
    /** @var array<int, bool> */
    public array $checkedItems = [];

    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(config('orders.default_planning_days', 7))->format('Y-m-d');
        $this->shoppingList = collect();
    }

    public function generateShoppingList(): void
    {
        // Get all order items within the date range
        $orderItems = Order::query()
            ->with(['orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$this->startDate, $this->endDate])
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Baking])
            ->get()
            ->flatMap(function (Order $order) {
                return $order->orderItems;
            });

        $aggregatedIngredients = collect();

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->product;
            $quantity = $orderItem->quantity;

            // Get recipes for this product
            foreach (($product->recipes ?? collect()) as $recipe) {
                if ($recipe->ingredients) {
                    foreach ($recipe->ingredients as $ingredient) {
                        $ingredientName = $ingredient['name'] ?? '';
                        $ingredientQuantity = $ingredient['quantity'] ?? 0;
                        $ingredientUnit = $ingredient['unit'] ?? '';

                        if ($ingredientName) {
                            $key = $ingredientName . '|' . $ingredientUnit;
                            $totalQuantity = ($ingredientQuantity * $quantity);

                            if ($aggregatedIngredients->has($key)) {
                                $aggregatedIngredients[$key]['quantity'] += $totalQuantity;
                            } else {
                                $aggregatedIngredients[$key] = [
                                    'name' => $ingredientName,
                                    'quantity' => $totalQuantity,
                                    'unit' => $ingredientUnit,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Cross-reference with ingredient inventory
        $inventoryIngredients = Ingredient::all()->keyBy(fn (Ingredient $i) => Str::lower($i->name));

        $aggregatedIngredients = $aggregatedIngredients->map(function (array $item) use ($inventoryIngredients) {
            $key = Str::lower($item['name']);
            $tracked = $inventoryIngredients->get($key);
            $item['in_stock'] = $tracked ? (float) $tracked->current_stock : null;
            $item['stock_unit'] = $tracked?->unit;
            $item['needs_purchase'] = $tracked ? $item['quantity'] > (float) $tracked->current_stock : true;
            $item['deficit'] = $tracked ? max(0, $item['quantity'] - (float) $tracked->current_stock) : $item['quantity'];

            return $item;
        });

        // Sort by ingredient name
        $this->shoppingList = $aggregatedIngredients->sortBy('name')->values();
        $this->checkedItems = [];
    }

    public function toggleItem(int $index): void
    {
        if (isset($this->checkedItems[$index])) {
            unset($this->checkedItems[$index]);
        } else {
            $this->checkedItems[$index] = true;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Shopping List')
                ->icon(Heroicon::OutlinedListBullet)
                ->action('generateShoppingList'),
            Action::make('print')
                ->label('Print')
                ->icon(Heroicon::OutlinedPrinter)
                ->action(fn () => $this->dispatch('print-shopping-list'))
                ->visible(fn () => $this->shoppingList->isNotEmpty()),
        ];
    }
}
