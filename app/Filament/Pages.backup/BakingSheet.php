<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use BackedEnum;

class BakingSheet extends Page
{
    protected string $view = 'filament.pages.baking-sheet';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlineDocumentText;
    
    protected static string|BackedEnum|null $navigationGroup = 'Operations';
    
    protected static ?string $title = 'Baking Sheet';
    
    public ?string $selectedDate = null;
    public array $orderItems = [];
    public array $aggregatedItems = [];
    
    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadBakingData();
    }
    
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedSchema::make()
                    ->form(function (Form $form) {
                        return $form
                            ->schema([
                                Section::make('Select Date')
                                    ->components([
                                        DatePicker::make('selectedDate')
                                            ->label('Baking Date')
                                            ->default(now())
                                            ->reactive()
                                            ->afterStateUpdated(fn () => $this->loadBakingData()),
                                    ]),
                            ]);
                    })
                    ->statePath(''),
            ]);
    }
    
    public function loadBakingData(): void
    {
        if (!$this->selectedDate) {
            return;
        }
        
        $date = Carbon::parse($this->selectedDate);
        
        // Get all orders for the selected date
        $orders = Order::with(['orderItems.product', 'customer'])
            ->whereDate('requested_date', $date)
            ->whereIn('status', ['confirmed', 'baking', 'ready'])
            ->get();
        
        $this->orderItems = [];
        $aggregated = [];
        
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $this->orderItems[] = [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'order_status' => $order->status,
                    'requested_time' => $order->requested_time,
                ];
                
                // Aggregate by product
                $productKey = $item->product->id;
                if (!isset($aggregated[$productKey])) {
                    $aggregated[$productKey] = [
                        'product_name' => $item->product->name,
                        'total_quantity' => 0,
                        'orders' => [],
                    ];
                }
                
                $aggregated[$productKey]['total_quantity'] += $item->quantity;
                $aggregated[$productKey]['orders'][] = [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                ];
            }
        }
        
        $this->aggregatedItems = array_values($aggregated);
    }
    
    public function getFormattedDate(): string
    {
        if (!$this->selectedDate) {
            return '';
        }
        
        return Carbon::parse($this->selectedDate)->format('l, F j, Y');
    }
}