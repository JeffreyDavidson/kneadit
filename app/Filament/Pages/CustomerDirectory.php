<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerNote;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CustomerDirectory extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Customer Directory';
    protected static ?string $title = 'Customer Directory';

    protected string $view = 'filament.pages.customer-directory';

    public string $search = '';
    public ?array $noteData = [];

    public function mount(): void
    {
        $this->noteForm->fill();
    }

    public function loadCustomerDetails($customerId)
    {
        return $this->getCustomerDetails($customerId);
    }

    public function noteForm(Form $form): Form
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

    public function getCustomers()
    {
        $query = Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->with(['orders' => function($query) {
                $query->latest()->take(1);
            }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('name')->get()->map(function($customer) {
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

    public function getCustomerDetails($customerId)
    {
        $customer = Customer::with([
            'orders' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'customerNotes' => function($query) {
                $query->with('createdBy')->orderBy('created_at', 'desc');
            }
        ])->find($customerId);

        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->full_address,
            'orders' => $customer->orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => ucfirst($order->status),
                    'payment_status' => ucfirst($order->payment_status),
                    'total' => number_format($order->total, 2),
                    'date' => $order->created_at->format('M j, Y'),
                    'requested_date' => $order->requested_date?->format('M j, Y'),
                ];
            }),
            'notes' => $customer->customerNotes->map(function($note) {
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
            ]
        ];
    }

    public function addNote($customerId)
    {
        $this->noteForm->validate();

        CustomerNote::create([
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

    public function updatedSearch()
    {
        // This will trigger a re-render when search changes
        $this->dispatch('searchUpdated');
    }

    protected function getViewData(): array
    {
        return [
            'customers' => $this->getCustomers(),
        ];
    }
}