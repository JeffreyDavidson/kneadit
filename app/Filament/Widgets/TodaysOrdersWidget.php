<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Date;

class TodaysOrdersWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 2;

    protected static ?string $heading = "Today's Orders";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereDate('delivery_date', Date::today())
                    ->orderBy('delivery_time'),
            )
            ->columns($this->columnSet())
            ->emptyStateHeading('No orders today — enjoy the quiet!')
            ->emptyStateDescription('Orders scheduled for today will appear here.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.orders.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        // todays_orders is constrained to md/lg in WidgetMeta — sm isn't an
        // option (a single-column compact view would lose the time-ordering
        // value of the widget). Two variants:
        // md → 3 essentials (time, customer, total)
        // lg → full detail (+ order #, delivery type, status badges)

        $time = TextColumn::make('delivery_time')->label('Time')->time('g:i A');
        $customer = TextColumn::make('customer_name')->label('Customer');
        $total = TextColumn::make('total')->money('usd');

        if ($this->isSize('md')) {
            return [$time, $customer, $total];
        }

        return [
            TextColumn::make('order_number')->label('Order #'),
            $time,
            $customer,
            TextColumn::make('delivery_type')->label('Type')->badge(),
            TextColumn::make('status')->badge(),
            $total,
        ];
    }
}
