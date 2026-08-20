<?php

namespace App\Filament\Pages\Tools;

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

    /** @var Collection<int|string, array<string, mixed>> */
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
        $this->supplierGroups = new Collection(resolve(ShoppingListService::class)->generate(
            includeUpcoming: $this->includeUpcoming,
            startDate: $this->startDate,
            endDate: $this->endDate,
        ));
    }

    public function toggleUpcoming(): void
    {
        $this->includeUpcoming = ! $this->includeUpcoming;
        $this->generateList();
    }

    public function sendPurchaseOrder(int $supplierId): void
    {
        $group = $this->purchaseOrderGroup($this->supplierGroups->get($supplierId));

        if ($group === null) {
            Notification::make()
                ->title('No email address')
                ->body('This supplier does not have an email address configured.')
                ->danger()
                ->send();

            return;
        }

        $storeName = resolve(TenantSettings::class)->store->name;

        $leadTimeDays = 3;

        foreach ($group['items'] as $item) {
            $leadTime = filter_var($item['lead_time_days'] ?? null, FILTER_VALIDATE_INT);

            if (is_int($leadTime)) {
                $leadTimeDays = max($leadTimeDays, $leadTime);
            }
        }

        event(new PurchaseOrderRequested(
            supplierEmail: $group['supplier']['email'],
            supplierName: $group['supplier']['name'],
            storeName: $storeName,
            items: $group['items'],
            total: $group['total'],
            requestedDate: now()->addDays($leadTimeDays)->format('Y-m-d'),
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
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(fn () => $this->generateList()),
        ];
    }

    /**
     * @return array{supplier: array{name: string, email: string}, items: list<array<string, mixed>>, total: float}|null
     */
    private function purchaseOrderGroup(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $supplier = $value['supplier'] ?? null;
        $rawItems = $value['items'] ?? null;
        $total = $value['total'] ?? null;

        if (! is_array($supplier) || ! is_array($rawItems) || ! is_numeric($total)) {
            return null;
        }

        $name = $supplier['name'] ?? null;
        $email = $supplier['email'] ?? null;

        if (! is_string($name) || ! is_string($email) || $email === '') {
            return null;
        }

        $items = [];

        foreach ($rawItems as $rawItem) {
            if (! is_array($rawItem)) {
                continue;
            }

            $item = [];

            foreach ($rawItem as $key => $itemValue) {
                if (is_string($key)) {
                    $item[$key] = $itemValue;
                }
            }

            $items[] = $item;
        }

        return [
            'supplier' => ['name' => $name, 'email' => $email],
            'items' => $items,
            'total' => floatval($total),
        ];
    }
}
