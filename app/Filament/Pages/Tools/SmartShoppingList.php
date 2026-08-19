<?php

namespace App\Filament\Pages\Tools;

use App\DataTransferObjects\Inventory\SupplierShoppingList;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Marketing\PurchaseOrderRequested;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Inventory\ShoppingListService;
use App\Services\Settings\TenantSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;

class SmartShoppingList extends Page
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

    protected string $view = 'filament.pages.tools.smart-shopping-list';

    public string $startDate = '';

    public string $endDate = '';

    public bool $includeUpcoming = false;

    /** @var Collection<int|string, array{supplier: array{id: ?int, name: string, email: ?string, phone: ?string}, items: array<int, array{ingredient_id: int, name: string, unit: string, current_stock: float, needed: float, unit_price: float, subtotal: float, sku: ?string, minimum_order: ?float, lead_time_days: ?int}>, total: float}> */
    public Collection $supplierGroups;

    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(Config::integer('orders.default_planning_days', 7))->format('Y-m-d');
        $this->supplierGroups = new Collection;
        $this->generateList();
    }

    public function generateList(): void
    {
        $this->supplierGroups = resolve(ShoppingListService::class)
            ->generate(
                includeUpcoming: $this->includeUpcoming,
                startDate: $this->startDate,
                endDate: $this->endDate,
            )
            ->map(fn (SupplierShoppingList $group): array => $group->toArray());
    }

    public function toggleUpcoming(): void
    {
        $this->includeUpcoming = ! $this->includeUpcoming;
        $this->generateList();
    }

    public function sendPurchaseOrder(int $supplierId): void
    {
        $group = resolve(ShoppingListService::class)
            ->generate(
                includeUpcoming: $this->includeUpcoming,
                startDate: $this->startDate,
                endDate: $this->endDate,
            )
            ->get($supplierId);

        if (! $group instanceof SupplierShoppingList || ! $group->canRequestPurchaseOrder()) {
            Notification::make()
                ->title('No email address')
                ->body('This supplier does not have an email address configured.')
                ->danger()
                ->send();

            return;
        }

        $storeName = resolve(TenantSettings::class)->store->name;

        event(new PurchaseOrderRequested(
            supplierEmail: $group->supplier->email ?? throw new \LogicException('Supplier email is required.'),
            supplierName: $group->supplier->name,
            storeName: $storeName,
            items: $group->items->map(fn ($item): array => $item->toArray())->all(),
            total: $group->total(),
            requestedDate: now()->addDays($group->maximumLeadTimeDays())->format('Y-m-d'),
        ));

        Notification::make()
            ->title('Purchase order sent!')
            ->body("Email sent to {$group->supplier->email}")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(fn () => $this->generateList()),
        ];
    }
}
