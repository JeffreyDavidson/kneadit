<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Mail\PurchaseOrder;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Pennant\Feature;

class SmartShoppingList extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Shopping List';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.smart-shopping-list';

    public string $startDate = '';

    public string $endDate = '';

    public bool $includeUpcoming = false;

    public Collection $supplierGroups;

    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(7)->format('Y-m-d');
        $this->supplierGroups = collect();
        $this->generateList();
    }

    public function generateList(): void
    {
        // Get ingredients below low stock threshold
        $lowStockIngredients = Ingredient::query()->where(function (Builder $q) {
            $q->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        })->with('suppliers')->get();

        // If including upcoming orders, calculate additional needs
        $upcomingNeeds = collect();
        if ($this->includeUpcoming && $this->startDate && $this->endDate) {
            $orders = Order::query()->whereBetween('pickup_date', [$this->startDate, $this->endDate])
                ->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Delivered])
                ->with('orderItems.product.recipe.inventoryIngredients')
                ->get();

            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    if ($item->product?->recipe) {
                        foreach ($item->product->recipe->inventoryIngredients as $ingredient) {
                            $needed = ($ingredient->pivot->quantity ?? 0) * $item->quantity;
                            $upcomingNeeds[$ingredient->id] = ($upcomingNeeds[$ingredient->id] ?? 0) + $needed;
                        }
                    }
                }
            }
        }

        // Build grouped list by supplier
        $grouped = [];
        $noSupplier = [];

        foreach ($lowStockIngredients as $ingredient) {
            $neededQty = max(0, ($ingredient->low_stock_threshold * 2) - $ingredient->current_stock);
            $neededQty += $upcomingNeeds[$ingredient->id] ?? 0;

            if ($neededQty <= 0) {
                continue;
            }

            $bestSupplier = $ingredient->suppliers
                ->where('is_active', true)
                ->sortBy('pivot.unit_price')
                ->first();

            /** @var object{unit_price?: string, minimum_order?: string, lead_time_days?: int, sku?: string}|null $pivot */
            $pivot = $bestSupplier?->pivot;

            $item = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => $ingredient->current_stock,
                'needed' => round($neededQty, 2),
                'unit_price' => $pivot->unit_price ?? $ingredient->cost_per_unit ?? 0,
                'subtotal' => round($neededQty * ($pivot->unit_price ?? $ingredient->cost_per_unit ?? 0), 2),
                'sku' => $pivot?->sku,
                'minimum_order' => $pivot?->minimum_order,
                'lead_time_days' => $pivot?->lead_time_days,
            ];

            if ($bestSupplier) {
                $grouped[$bestSupplier->id]['supplier'] = [
                    'id' => $bestSupplier->id,
                    'name' => $bestSupplier->name,
                    'email' => $bestSupplier->email,
                    'phone' => $bestSupplier->phone,
                ];
                $grouped[$bestSupplier->id]['items'][] = $item;
                $grouped[$bestSupplier->id]['total'] = collect($grouped[$bestSupplier->id]['items'])->sum('subtotal');
            } else {
                $noSupplier[] = $item;
            }
        }

        if (! empty($noSupplier)) {
            $grouped['none'] = [
                'supplier' => [
                    'id' => null,
                    'name' => 'No Supplier Assigned',
                    'email' => null,
                    'phone' => null,
                ],
                'items' => $noSupplier,
                'total' => collect($noSupplier)->sum('subtotal'),
            ];
        }

        $this->supplierGroups = collect($grouped);
    }

    public function toggleUpcoming(): void
    {
        $this->includeUpcoming = ! $this->includeUpcoming;
        $this->generateList();
    }

    public function sendPurchaseOrder(int $supplierId): void
    {
        $group = $this->supplierGroups->get($supplierId);

        if (! $group || ! $group['supplier']['email']) {
            Notification::make()
                ->title('No email address')
                ->body('This supplier does not have an email address configured.')
                ->danger()
                ->send();

            return;
        }

        $storeName = Setting::get('store_name', config('app.name'));

        Mail::to($group['supplier']['email'])
            ->send(new PurchaseOrder(
                supplierName: $group['supplier']['name'],
                storeName: $storeName,
                items: $group['items'],
                total: $group['total'],
                requestedDate: now()->addDays(
                    collect($group['items'])->max('lead_time_days') ?? 3
                )->format('Y-m-d'),
            ));

        Notification::make()
            ->title('Purchase order sent!')
            ->body("Email sent to {$group['supplier']['email']}")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->generateList()),
        ];
    }
}
