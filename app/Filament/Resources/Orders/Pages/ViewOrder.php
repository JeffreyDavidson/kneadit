<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Orders\AddOrderNote;
use App\Actions\Orders\SendOrderMessage;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\SenderType;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Order $record
 */
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
                                    ->badge(),
                                TextEntry::make('payment_status')
                                    ->badge(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('delivery_date')
                                    ->date(),
                                TextEntry::make('delivery_time')
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
                        Grid::make(6)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->money('USD'),
                                TextEntry::make('delivery_fee')
                                    ->money('USD'),
                                TextEntry::make('discount_amount')
                                    ->money('USD'),
                                TextEntry::make('gift_card_amount')
                                    ->money('USD'),
                                TextEntry::make('tip_amount')
                                    ->label('Tip')
                                    ->money('USD'),
                                TextEntry::make('total')
                                    ->money('USD')
                                    ->weight(FontWeight::Bold),
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
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->schema([
                        ViewEntry::make('messages_view')
                            ->view('filament.resources.orders.view-order-messages')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $allowedTransitions = TransitionOrderStatus::allowedTransitions($this->record);
        $options = collect($allowedTransitions)
            ->mapWithKeys(fn (OrderStatus $status) => [$status->value => $status->name])
            ->all();

        return [
            Action::make('changeStatus')
                ->label('Change Status')
                ->icon(Heroicon::OutlinedArrowPath)
                ->visible(fn (): bool => ! empty($options))
                ->schema([
                    Select::make('status')
                        ->label('New Status')
                        ->options($options)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        resolve(TransitionOrderStatus::class)($this->record, OrderStatus::from($data['status']));
                        Notification::make()->title('Order status updated successfully.')->success()->send();
                    } catch (InvalidOrderTransitionException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('sendPayPalInvoice')
                ->label('Send PayPal Invoice')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('info')
                ->visible(fn (): bool => ! $this->record->paypal_invoice_id)
                ->action(function (): void {
                    // This would integrate with PayPal service
                    Notification::make()->title('PayPal invoice functionality coming soon.')->info()->send();
                }),

            Action::make('printInvoice')
                ->label('Print Invoice')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('admin.orders.invoice', $this->record))
                ->openUrlInNewTab(),

            Action::make('sendMessage')
                ->label('Send Message')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success')
                ->schema([
                    Textarea::make('message')
                        ->label('Message to Customer')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $bakerName = auth()->user()->name ?? app(TenantSettings::class)->store->name;

                    resolve(SendOrderMessage::class)(
                        order: $this->record,
                        senderName: $bakerName,
                        message: $data['message'],
                        senderType: SenderType::Baker,
                    );

                    Notification::make()->title('Message sent to customer.')->success()->send();
                }),

            Action::make('addNote')
                ->label('Add Note')
                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                ->schema([
                    Textarea::make('note')
                        ->label('Note')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    resolve(AddOrderNote::class)($this->record, $data['note']);

                    Notification::make()->title('Note added successfully.')->success()->send();
                }),
        ];
    }
}
