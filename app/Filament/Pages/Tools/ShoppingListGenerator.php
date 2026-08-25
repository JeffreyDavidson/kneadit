<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Orders\OrderIngredientAggregator;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;

class ShoppingListGenerator extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Shopping List';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.tools.shopping-list-generator';

    public string $startDate = '';

    public string $endDate = '';

    /** @var Collection<int, array{name: string, quantity: float, unit: string, in_stock: float|null, stock_unit: string|null, needs_purchase: bool, deficit: float}> */
    public Collection $shoppingList;

    /** @var array<int, bool> */
    public array $checkedItems = [];

    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(Config::integer('orders.default_planning_days', 7))->format('Y-m-d');
        $this->shoppingList = new Collection;
    }

    public function generateShoppingList(OrderIngredientAggregator $service): void
    {
        $this->shoppingList = $service->generate($this->startDate, $this->endDate);
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

    protected function getHeaderActions(): array
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
