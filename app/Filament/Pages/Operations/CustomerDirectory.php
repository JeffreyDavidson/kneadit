<?php

namespace App\Filament\Pages\Operations;

use App\Actions\Customers\AddCustomerNote;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customers\Customer;
use App\Presenters\CustomerPresenter;
use App\Queries\Customers\CustomerDirectoryQuery;
use App\Queries\Customers\CustomerDirectoryStatsQuery;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;
use Laravel\Pennant\Feature;

/**
 * @property-read Schema $noteForm
 */
class CustomerDirectory extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Customer Directory';

    protected static ?string $title = 'Customer Directory';

    protected string $view = 'filament.pages.operations.customer-directory';

    public string $search = '';

    /** @var array<string, mixed> */
    public ?array $noteData = [];

    public function mount(): void
    {
        $this->noteForm->fill();
    }

    /** @return array<string, mixed> */
    public function loadCustomerDetails(int $customerId): ?array
    {
        return $this->getCustomerDetails($customerId);
    }

    public function noteForm(Schema $form): Schema
    {
        return $form
            ->schema([
                Textarea::make('note')
                    ->label('Add Note')
                    ->placeholder('Enter a note about this customer...')
                    ->required()
                    ->rows(3),
            ])
            ->statePath('noteData');
    }

    /** @return array<string, mixed> */
    public function getStats(): array
    {
        return CustomerDirectoryStatsQuery::get();
    }

    /** @return Collection<int, array{id: int, name: string, email: string, phone: string, total_orders: int|null, total_spent: string|false, last_order_date: non-falsy-string}> */
    public function getCustomers(): Collection
    {
        return CustomerDirectoryQuery::search($this->search)->map(function (Customer $customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'total_orders' => $customer->orders_count,
                // orders.total is bigint cents (migration 2026_04_22_201500); withSum bypasses
                // MoneyCentsCast and returns the raw cents, so divide back to dollars here.
                'total_spent' => Number::currency(((int) ($customer->orders_sum_total ?? 0)) / 100),
                'last_order_date' => $customer->last_order_date
                    ? Date::parse($customer->last_order_date)->format('M j, Y')
                    : 'Never',
            ];
        });
    }

    /** @return array<string, mixed> */
    public function getCustomerDetails(int $customerId): ?array
    {
        $customer = CustomerDirectoryQuery::findWithDetails($customerId);

        if (! $customer) {
            return null;
        }

        return CustomerPresenter::for($customer)->toDetailArray();
    }

    public function addNote(int $customerId): void
    {
        $state = $this->noteForm->getState();
        $note = $state['note'] ?? null;

        if (! is_string($note)) {
            throw new \InvalidArgumentException('A customer note is required.');
        }

        resolve(AddCustomerNote::class)(
            $customerId,
            $note,
            (int) Auth::id(),
        );

        $this->noteData = [];
        $this->noteForm->fill();

        Notification::make()
            ->title('Note added successfully')
            ->success()
            ->send();

        // Dispatch event to refresh customer details
        $this->dispatch('refreshCustomerDetails');
    }

    public function updatedSearch(): void
    {
        // This will trigger a re-render when search changes
        $this->dispatch('searchUpdated');
    }

    protected function getViewData(): array
    {
        return [
            'customers' => $this->getCustomers(),
            'stats' => $this->getStats(),
        ];
    }
}
