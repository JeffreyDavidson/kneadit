<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Mail\PurchaseOrder;
use App\Services\Inventory\ShoppingListService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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

    /** @var Collection<int|string, mixed> */
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
        $group = $this->supplierGroups->get($supplierId);

        if (! $group || ! $group['supplier']['email']) {
            Notification::make()
                ->title('No email address')
                ->body('This supplier does not have an email address configured.')
                ->danger()
                ->send();

            return;
        }

        $storeName = settings('store_name', config('app.name'));

        Mail::to($group['supplier']['email'])
            ->send(new PurchaseOrder(
                supplierName: $group['supplier']['name'],
                storeName: $storeName,
                items: $group['items'],
                total: $group['total'],
                requestedDate: now()->addDays(
                    (int) max(3, ...array_column($group['items'], 'lead_time_days')),
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
