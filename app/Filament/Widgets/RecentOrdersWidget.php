<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 4;

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with('customer')->latest()->limit($this->rowLimit()),
            )
            ->columns($this->columnSet())
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.orders.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            WidgetSize::Large => 10,
        };
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        $base = [
            TextColumn::make('order_number')
                ->label('Order')
                ->url(fn (Order $record) => route('filament.admin.resources.orders.view', $record))
                ->color('primary'),
            TextColumn::make('customer.name')
                ->label('Customer')
                ->limit(18),
            TextColumn::make('total')
                ->money('usd'),
        ];

        if ($this->isSize('sm')) {
            return $base;
        }

        $base[] = TextColumn::make('status')->badge();

        if ($this->isSize('md')) {
            return $base;
        }

        $base[] = TextColumn::make('created_at')->label('When')->since();

        return $base;
    }
}
