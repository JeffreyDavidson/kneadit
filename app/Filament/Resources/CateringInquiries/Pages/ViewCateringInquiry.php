<?php

namespace App\Filament\Resources\CateringInquiries\Pages;

use App\Actions\Customers\CancelCateringInquiry;
use App\Actions\Customers\ConvertCateringInquiryToOrder;
use App\Actions\Customers\RecordCateringDeposit;
use App\Actions\Customers\ResendCateringQuote;
use App\Actions\Customers\SendCateringQuote;
use App\Enums\Customers\CateringInquiryStatus;
use App\Exceptions\Customers\InquiryNotConvertibleException;
use App\Filament\Forms\Components\ContactFields;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read CateringInquiry $record
 */
class ViewCateringInquiry extends ViewRecord
{
    protected static string $resource = CateringInquiryResource::class;

    protected string $view = 'filament.resources.catering-inquiries.view-catering-inquiry';

    protected function getHeaderActions(): array
    {
        return [
            $this->cancelAction(),
        ];
    }

    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel inquiry')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->authorize('update')
            ->requiresConfirmation()
            ->modalHeading('Cancel this catering inquiry?')
            ->modalDescription('Status will be set to Cancelled. The customer is not notified automatically.')
            ->visible(fn (): bool => ! in_array($this->record->status, [CateringInquiryStatus::Completed, CateringInquiryStatus::Cancelled], true))
            ->schema([
                Textarea::make('reason')
                    ->label('Reason (optional, recorded in notes)')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                resolve(CancelCateringInquiry::class)($this->record, $data['reason'] ?? null);

                $this->record->refresh();

                Notification::make()->title('Inquiry cancelled.')->success()->send();
            });
    }

    public function editCustomerAction(): Action
    {
        return Action::make('editCustomer')
            ->label('Edit')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('gray')
            ->size('xs')
            ->slideOver()
            ->authorize('update')
            ->fillForm(fn (): array => [
                'customer_name' => $this->record->customer_name,
                'customer_email' => $this->record->customer_email,
                'customer_phone' => $this->record->customer_phone,
            ])
            ->schema(ContactFields::nameEmailPhone())
            ->action(function (array $data): void {
                $this->record->update($data);

                Notification::make()->title('Customer updated.')->success()->send();
            });
    }

    public function editEventDetailsAction(): Action
    {
        return Action::make('editEventDetails')
            ->label('Edit')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('gray')
            ->size('xs')
            ->slideOver()
            ->authorize('update')
            ->fillForm(fn (): array => [
                'event_type' => $this->record->event_type,
                'event_date' => $this->record->event_date?->toDateString(),
                'guest_count' => $this->record->guest_count,
                'budget' => $this->record->budget?->dollars(),
                'details' => $this->record->details,
                'dietary_requirements' => $this->record->dietary_requirements,
                'venue_address' => $this->record->venue_address,
            ])
            ->schema([
                Select::make('event_type')
                    ->options(function () {
                        $types = resolve(TenantSettings::class)->catering->eventTypes;

                        return array_combine($types, $types);
                    })
                    ->required(),
                DatePicker::make('event_date')->required(),
                TextInput::make('guest_count')->numeric()->required()->minValue(1),
                MoneyInput::make('budget')->placeholder('Optional'),
                Textarea::make('details')->required()->rows(4)->label('What they want'),
                Textarea::make('dietary_requirements')->rows(2),
                Textarea::make('venue_address')->rows(2),
            ])
            ->action(function (array $data): void {
                $this->record->update($data);

                Notification::make()->title('Event details updated.')->success()->send();
            });
    }

    public function manageQuoteItemsAction(): Action
    {
        return Action::make('manageQuoteItems')
            ->label('Manage items')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->color('gray')
            ->size('xs')
            ->slideOver()
            ->authorize('update')
            ->visible(fn (): bool => in_array($this->record->status, [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted], true))
            ->modalDescription('Add, edit, or reorder items that make up this quote. The total auto-recomputes; the customer is not emailed.')
            ->fillForm(fn (): array => [
                'items' => $this->record->items->map(fn (CateringInquiryItem $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price->dollars(),
                    'special_instructions' => $item->special_instructions,
                    'sort_order' => $item->sort_order,
                ])->all(),
            ])
            ->schema([
                Repeater::make('items')
                    ->label('Quote items')
                    ->reorderable()
                    ->reorderableWithDragAndDrop()
                    ->defaultItems(0)
                    ->addActionLabel('Add item')
                    ->columns(4)
                    ->schema([
                        TextInput::make('name')->required()->columnSpan(2),
                        TextInput::make('quantity')->numeric()->required()->minValue(1)->default(1),
                        MoneyInput::make('unit_price')->required(),
                        Textarea::make('special_instructions')->rows(2)->columnSpanFull(),
                    ]),
            ])
            ->action(function (array $data): void {
                $rows = is_array($data['items'] ?? null) ? $data['items'] : [];

                $existing = $this->record->items()->get()->keyBy('id');
                $submittedIds = array_values(array_filter(array_map(
                    fn (mixed $row): ?int => is_array($row) && ! empty($row['id']) ? (int) $row['id'] : null,
                    $rows,
                )));

                foreach ($existing as $id => $item) {
                    if (! in_array($id, $submittedIds, true)) {
                        $item->delete();
                    }
                }

                foreach (array_values($rows) as $sortOrder => $row) {
                    $payload = [
                        'name' => $row['name'],
                        'quantity' => (int) $row['quantity'],
                        'unit_price' => $row['unit_price'],
                        'special_instructions' => $row['special_instructions'] ?: null,
                        'sort_order' => $sortOrder,
                    ];

                    if (! empty($row['id'])) {
                        $existingItem = $existing->get((int) $row['id']);

                        if ($existingItem instanceof CateringInquiryItem) {
                            $existingItem->update($payload);

                            continue;
                        }
                    }

                    $this->record->items()->create($payload);
                }

                // Defensive recompute: the observer covers per-item CRUD, but
                // the Filament Repeater path bulk-mutates and we want the
                // parent total guaranteed in sync before the page rerenders.
                $sumCents = $this->record->items()->get()
                    ->sum(fn (CateringInquiryItem $i): int => $i->unit_price->cents() * $i->quantity);

                $this->record->update(['quoted_amount' => Money::fromCents((int) $sumCents)]);

                $this->record->refresh();

                Notification::make()->title('Quote items updated.')->success()->send();
            });
    }

    public function sendQuoteAction(): Action
    {
        return Action::make('sendQuote')
            ->label('Send quote')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('info')
            ->authorize('update')
            ->visible(fn (): bool => $this->record->status === CateringInquiryStatus::Inquiry && $this->record->quoted_amount !== null)
            ->requiresConfirmation()
            ->modalHeading('Send quote to customer')
            ->modalDescription(fn (): string => "Email a quote of {$this->record->quoted_amount?->formatted()} to {$this->record->customer_email}.")
            ->action(function (): void {
                resolve(SendCateringQuote::class)($this->record);

                $this->record->refresh();

                Notification::make()->title('Quote sent.')->success()->send();
            });
    }

    public function resendQuoteAction(): Action
    {
        return Action::make('resendQuote')
            ->label('Resend')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->authorize('update')
            ->visible(fn (): bool => $this->record->status === CateringInquiryStatus::Quoted)
            ->requiresConfirmation()
            ->modalHeading('Resend the current quote?')
            ->modalDescription(fn (): string => "Re-emails the {$this->record->quoted_amount?->formatted()} quote to {$this->record->customer_email}.")
            ->action(function (): void {
                resolve(ResendCateringQuote::class)($this->record);

                Notification::make()->title('Quote resent.')->success()->send();
            });
    }

    public function confirmBookingAction(): Action
    {
        return Action::make('confirmBooking')
            ->label('Confirm booking')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->authorize('update')
            ->visible(fn (): bool => $this->record->status === CateringInquiryStatus::Quoted && $this->record->order()->doesntExist())
            ->requiresConfirmation()
            ->modalHeading('Confirm this booking?')
            ->modalDescription('Creates an order so the rest of fulfillment (payment, messages, status) is tracked there.')
            ->action(function (): void {
                try {
                    $order = resolve(ConvertCateringInquiryToOrder::class)($this->record);
                } catch (InquiryNotConvertibleException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();

                    return;
                }

                $this->record->refresh();

                Notification::make()
                    ->title('Booking confirmed.')
                    ->body("Order {$order->order_number} created.")
                    ->success()
                    ->send();
            });
    }

    public function markDepositReceivedAction(): Action
    {
        return Action::make('markDepositReceived')
            ->label('Mark deposit received')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->authorize('update')
            ->visible(fn (): bool => $this->record->deposit_paid_at === null
                && in_array($this->record->status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed], true))
            ->slideOver()
            ->schema([
                TextInput::make('amount')
                    ->label('Deposit amount ($)')
                    ->numeric()
                    ->required()
                    ->default(fn (): float => resolve(RecordCateringDeposit::class)->suggestedAmount(
                        $this->record,
                        resolve(TenantSettings::class)->catering->depositPercent,
                    )),
                TextInput::make('reference')
                    ->label('Reference (check #, last-4, etc.)')
                    ->maxLength(255),
            ])
            ->action(function (array $data): void {
                resolve(RecordCateringDeposit::class)(
                    $this->record,
                    (float) $data['amount'],
                    $data['reference'] ?? null,
                );

                $this->record->refresh();

                Notification::make()->title('Deposit recorded.')->success()->send();
            });
    }

    public function editNotesAction(): Action
    {
        return Action::make('editNotes')
            ->label('Edit')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('gray')
            ->size('xs')
            ->slideOver()
            ->authorize('update')
            ->fillForm(fn (): array => ['notes' => $this->record->notes])
            ->schema([
                Textarea::make('notes')
                    ->label('Internal notes')
                    ->rows(8)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $this->record->update(['notes' => $data['notes']]);

                Notification::make()->title('Notes updated.')->success()->send();
            });
    }
}
