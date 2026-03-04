<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\Order;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class CustomerDirectory extends Page
{
    protected string $view = 'filament.pages.customer-directory';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlineUsers;
    
    protected static string|BackedEnum|null $navigationGroup = 'Sales';
    
    protected static ?string $title = 'Customer Directory';
    
    public ?string $search = '';
    public array $customers = [];
    public ?array $selectedCustomer = null;
    public array $customerOrders = [];
    public array $customerNotes = [];
    
    public function mount(): void
    {
        $this->loadCustomers();
    }
    
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedSchema::make()
                    ->form(function (Form $form) {
                        return $form
                            ->schema([
                                Section::make('Search')
                                    ->components([
                                        TextInput::make('search')
                                            ->label('Search Customers')
                                            ->placeholder('Search by name, email, or phone...')
                                            ->reactive()
                                            ->afterStateUpdated(fn () => $this->loadCustomers()),
                                    ]),
                            ]);
                    })
                    ->statePath(''),
            ]);
    }
    
    public function loadCustomers(): void
    {
        $query = Customer::query()->withCount(['orders']);
        
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->customers = $query->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'orders_count' => $customer->orders_count,
                    'created_at' => $customer->created_at->format('M j, Y'),
                ];
            })
            ->toArray();
    }
    
    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::with(['orders' => function ($query) {
            $query->latest()->limit(10);
        }])->find($customerId);
        
        if (!$customer) {
            return;
        }
        
        $this->selectedCustomer = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'created_at' => $customer->created_at,
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->sum('total'),
            'last_order' => $customer->orders()->latest()->first()?->created_at,
        ];
        
        $this->customerOrders = $customer->orders->map(function (Order $order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'requested_date' => $order->requested_date,
                'created_at' => $order->created_at,
            ];
        })->toArray();
        
        $this->customerNotes = CustomerNote::where('customer_id', $customerId)
            ->with('createdBy')
            ->latest()
            ->get()
            ->map(function (CustomerNote $note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'created_by' => $note->createdBy->name,
                    'created_at' => $note->created_at,
                ];
            })
            ->toArray();
    }
    
    public function closeCustomerDetail(): void
    {
        $this->selectedCustomer = null;
        $this->customerOrders = [];
        $this->customerNotes = [];
    }
}