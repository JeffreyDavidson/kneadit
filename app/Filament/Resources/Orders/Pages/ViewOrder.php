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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Order $record
 */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.view-order';

    /**
     * The view template walks orderItems → product, customer, and
     * messages — strict-mode preventLazyLoading 500s if any of those
     * aren't loaded. Override getRecord() so the relations are
     * idempotently loaded on every render (initial mount AND every
     * Livewire follow-up request, where the model is re-hydrated from
     * the request payload without its prior relations).
     */
    public function getRecord(): Model
    {
        return parent::getRecord()->loadMissing(['orderItems.product', 'customer', 'messages']);
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
                    // Stub — real send flow is on the OrdersTable row action.
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
                    $bakerName = auth()->user()->name ?? resolve(TenantSettings::class)->store->name;

                    resolve(SendOrderMessage::class)(
                        order: $this->record,
                        senderName: $bakerName,
                        message: $data['message'],
                        senderType: SenderType::Baker,
                    );

                    $this->record->load('messages');

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

                    $this->record->refresh();

                    Notification::make()->title('Note added successfully.')->success()->send();
                }),
        ];
    }
}
