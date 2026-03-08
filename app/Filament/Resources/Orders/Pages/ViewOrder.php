<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\NewOrderMessage;
use App\Models\Order;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Order Number')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'gray',
                                        'confirmed' => 'info',
                                        'baking' => 'warning',
                                        'ready' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('payment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('requested_date')
                                    ->date(),
                                TextEntry::make('requested_time')
                                    ->time('H:i'),
                            ]),
                    ]),

                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer Name'),
                                TextEntry::make('customer.email')
                                    ->label('Email'),
                            ]),
                        TextEntry::make('delivery_address')
                            ->label('Delivery Address')
                            ->placeholder('No delivery address')
                            ->columnSpanFull(),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        ViewEntry::make('order_items')
                            ->view('filament.resources.orders.view-order-items')
                            ->columnSpanFull(),
                    ]),

                Section::make('Financial Summary')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->money('USD'),
                                TextEntry::make('delivery_fee')
                                    ->money('USD'),
                                TextEntry::make('discount')
                                    ->money('USD'),
                                TextEntry::make('total')
                                    ->money('USD')
                                    ->weight('bold'),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->badge(),
                                TextEntry::make('paypal_invoice_id')
                                    ->label('PayPal Invoice ID')
                                    ->placeholder('No PayPal invoice'),
                            ]),
                        TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Messages')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        ViewEntry::make('messages_view')
                            ->view('filament.resources.orders.view-order-messages')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeStatus')
                ->label('Change Status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('New Status')
                        ->options([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'baking' => 'Baking',
                            'ready' => 'Ready for Pickup/Delivery',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default($this->record->status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['status' => $data['status']]);
                    
                    $this->notify('success', 'Order status updated successfully.');
                }),

            Action::make('sendPayPalInvoice')
                ->label('Send PayPal Invoice')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn (): bool => !$this->record->paypal_invoice_id)
                ->action(function (): void {
                    // This would integrate with PayPal service
                    $this->notify('info', 'PayPal invoice functionality coming soon.');
                }),

            Action::make('printInvoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => route('admin.orders.invoice', $this->record))
                ->openUrlInNewTab(),

            Action::make('sendMessage')
                ->label('Send Message')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Textarea::make('message')
                        ->label('Message to Customer')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $bakerName = auth()->user()->name ?? Setting::get('store_name', 'Baker');

                    $message = $this->record->messages()->create([
                        'sender_type' => 'baker',
                        'sender_name' => $bakerName,
                        'message' => $data['message'],
                    ]);

                    // Mark customer messages as read
                    $this->record->messages()
                        ->where('sender_type', 'customer')
                        ->where('is_read', false)
                        ->update(['is_read' => true]);

                    // Email customer
                    $customerEmail = $this->record->customer?->email;
                    if ($customerEmail) {
                        Mail::to($customerEmail)->send(new NewOrderMessage($message));
                    }

                    $this->notify('success', 'Message sent to customer.');
                }),

            Action::make('addNote')
                ->label('Add Note')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->form([
                    \Filament\Forms\Components\Textarea::make('note')
                        ->label('Note')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $currentNotes = $this->record->notes;
                    $timestamp = now()->format('Y-m-d H:i:s');
                    $newNote = "[{$timestamp}] " . $data['note'];
                    
                    $updatedNotes = $currentNotes ? $currentNotes . "\n\n" . $newNote : $newNote;
                    
                    $this->record->update(['notes' => $updatedNotes]);
                    
                    $this->notify('success', 'Note added successfully.');
                }),
        ];
    }
}