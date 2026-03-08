<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;
use Filament\Actions\Action;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Traits\HasPlanGating;
class BakingSheet extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Baking Sheet';
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.baking-sheet';

    public string $selectedDate = '';
    public Collection $bakingItems;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadBakingSheet();
    }

    public function loadBakingSheet()
    {
        // Aggregate order items by product for the selected date
        $this->bakingItems = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereDate('orders.requested_date', $this->selectedDate)
            ->whereIn('orders.status', ['confirmed', 'in_progress'])
            ->select([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('GROUP_CONCAT(customers.name SEPARATOR ", ") as customer_names')
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();
    }

    public function updatedSelectedDate()
    {
        $this->loadBakingSheet();
    }

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->action(fn () => $this->dispatch('print-page')),
        ];
    }
}