<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\Order;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class CustomerDirectory extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
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

    protected string $view = 'filament.pages.customer-directory';

    public string $search = '';

    public ?array $noteData = [];

    public function mount(): void
    {
        $this->noteForm->fill();
    }

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
        $totalCustomers = Customer::query()->count();
        $avgLifetimeValue = (float) Order::query()->active()
            ->selectRaw('AVG(customer_total) as avg_ltv')
            ->fromSub(
                Order::query()->active()->selectRaw('customer_id, SUM(total) as customer_total')->groupBy('customer_id'),
                'customer_totals'
            )
            ->value('avg_ltv');

        $atRiskDays = (int) Setting::get('at_risk_days', '30');
        $atRiskCount = Customer::query()
            ->whereHas('orders', fn (EloquentBuilder $q) => $q->where('status', '!=', OrderStatus::Cancelled))
            ->whereDoesntHave('orders', fn (EloquentBuilder $q) => $q->where('status', '!=', OrderStatus::Cancelled)->where('created_at', '>=', now()->subDays($atRiskDays)))
            ->count();

        $topCustomer = Customer::query()
            ->withSum(['orders' => fn ($q) => $q->active()], 'total')
            ->orderByDesc('orders_sum_total')
            ->first();

        return [
            'total_customers' => $totalCustomers,
            'avg_lifetime_value' => number_format($avgLifetimeValue, 2),
            'at_risk_count' => $atRiskCount,
            'top_customer_name' => $topCustomer->name ?? 'N/A',
            'top_customer_value' => number_format($topCustomer->orders_sum_total ?? 0, 2),
        ];
    }

    /** @return Collection<int, mixed> */
    public function getCustomers(): Collection
    {
        $query = Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->with(['orders' => function (EloquentBuilder $query) {
                $query->latest()->take(1);
            }]);

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->whereLike('name', '%'.$this->search.'%')
                    ->orWhereLike('email', '%'.$this->search.'%')
                    ->orWhereLike('phone', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('name')->get()->map(function (Customer $customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'total_orders' => $customer->orders_count,
                'total_spent' => number_format($customer->orders_sum_total ?? 0, 2),
                'last_order_date' => $customer->orders->first()?->created_at?->format('M j, Y') ?? 'Never',
            ];
        });
    }

    public function getCustomerDetails(int $customerId): ?array
    {
        $customer = Customer::with([
            'orders' => function (EloquentBuilder $query) {
                $query->latest();
            },
            'customerNotes' => function (EloquentBuilder $query) {
                $query->with('createdBy')->latest();
            },
        ])->find($customerId);

        if (! $customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->full_address,
            'orders' => $customer->orders->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => ucfirst($order->status->value),
                    'payment_status' => ucfirst($order->payment_status->value),
                    'total' => number_format($order->total, 2),
                    'date' => $order->created_at->format('M j, Y'),
                    'delivery_date' => $order->delivery_date?->format('M j, Y'),
                ];
            }),
            'notes' => $customer->customerNotes->map(function (CustomerNote $note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'created_by' => $note->createdBy->name ?? 'Unknown',
                    'created_at' => $note->created_at->format('M j, Y g:i A'),
                ];
            }),
            'stats' => [
                'total_orders' => $customer->orders->count(),
                'total_spent' => $customer->orders->sum('total'),
                'avg_order_value' => $customer->orders->count() > 0
                    ? $customer->orders->sum('total') / $customer->orders->count()
                    : 0,
                'last_order' => $customer->orders->first()?->created_at?->format('M j, Y'),
            ],
        ];
    }

    public function addNote(int $customerId): void
    {
        $this->noteForm->validate();

        CustomerNote::query()->create([
            'customer_id' => $customerId,
            'note' => $this->noteData['note'],
            'created_by' => Auth::id(),
        ]);

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
