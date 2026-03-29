<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\OrderItem;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BakingSheet extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static ?string $navigationLabel = 'Baking Sheet';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.baking-sheet';

    public string $selectedDate = '';

    /** @var Collection<int, mixed> */
    public Collection $bakingItems;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadBakingSheet();
    }

    public function loadBakingSheet(): void
    {
        // Aggregate order items by product for the selected date
        $groupConcat = DB::getDriverName() === 'sqlite'
            ? "group_concat(customers.name, ', ')"
            : "GROUP_CONCAT(customers.name SEPARATOR ', ')";

        $this->bakingItems = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereDate('orders.delivery_date', $this->selectedDate)
            ->whereIn('orders.status', [OrderStatus::Confirmed->value, OrderStatus::Baking->value])
            ->select([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw("{$groupConcat} as customer_names"),
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadBakingSheet();
    }

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon(Heroicon::OutlinedPrinter)
                ->action(fn () => $this->dispatch('print-page')),
        ];
    }
}
