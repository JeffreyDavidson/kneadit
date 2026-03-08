<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;
use Filament\Actions\Action;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Recipe;
use Illuminate\Support\Collection;

use App\Traits\HasPlanGating;
class ShoppingListGenerator extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }


    protected static string $requiredPlan = 'pro';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Shopping List';
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.shopping-list-generator';

    public string $startDate = '';
    public string $endDate = '';
    public Collection $shoppingList;
    public array $checkedItems = [];

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(7)->format('Y-m-d');
        $this->shoppingList = collect();
    }

    public function generateShoppingList()
    {
        // Get all order items within the date range
        $orderItems = Order::query()
            ->with(['orderItems.product.recipes'])
            ->whereBetween('requested_date', [$this->startDate, $this->endDate])
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems;
            });

        $aggregatedIngredients = collect();

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->product;
            $quantity = $orderItem->quantity;

            // Get recipes for this product
            foreach ($product->recipes as $recipe) {
                if ($recipe->ingredients && is_array($recipe->ingredients)) {
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
        $inventoryIngredients = Ingredient::all()->keyBy(fn ($i) => strtolower($i->name));

        $aggregatedIngredients = $aggregatedIngredients->map(function ($item) use ($inventoryIngredients) {
            $key = strtolower($item['name']);
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

    public function toggleItem(int $index)
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
                ->icon('heroicon-o-list-bullet')
                ->action('generateShoppingList'),
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->action(fn () => $this->dispatch('print-shopping-list'))
                ->visible(fn () => $this->shoppingList->isNotEmpty()),
        ];
    }
}