<?php

namespace App\Filament\Resources\CateringInquiries\Pages;

use App\Actions\Customers\CancelCateringInquiry;
use App\Actions\Customers\ConvertCateringInquiryToOrder;
use App\Actions\Customers\RecordCateringDeposit;
use App\Actions\Customers\ResendCateringQuote;
use App\Actions\Customers\SendCateringQuote;
use App\Actions\Customers\SyncCateringQuoteItems;
use App\Actions\Customers\UpdateCateringCustomerDetails;
use App\Actions\Customers\UpdateCateringEventDetails;
use App\Actions\Customers\UpdateCateringInquiryNotes;
use App\DataTransferObjects\Customers\CateringEventDetails;
use App\Enums\Customers\CateringInquiryStatus;
use App\Exceptions\Customers\InquiryNotConvertibleException;
use App\Filament\Forms\Components\ContactFields;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Filament\Resources\CateringInquiries\Schemas\CateringEventDetailsFields;
use App\Filament\Resources\CateringInquiries\Support\CateringQuoteItemMapper;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\Services\Customers\CateringDepositCalculator;
use App\Services\Settings\TenantSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\ValidatedInput;

/**
 * @property-read CateringInquiry $record
 */
class ViewCateringInquiry extends ViewRecord
{
    #[\Override]
    protected static string $resource = CateringInquiryResource::class;

    #[\Override]
    protected string $view = 'filament.resources.catering-inquiries.view-catering-inquiry';

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->cancelAction(),
        ];
    }

    /** @return array<string, mixed> */
    #[\Override]
    protected function getViewData(): array
    {
        $inquiry = $this->record;
        $status = $inquiry->status;
        $eventDate = $inquiry->event_date;
        $eventCountdown = $eventDate?->isFuture()
            ? $eventDate->diffForHumans(['parts' => 1, 'short' => false])
            : null;
        $eventPast = $eventDate?->isPast() ?? false;
        $depositPaid = $inquiry->deposit_paid_at !== null;
        $depositPercent = app(TenantSettings::class)->catering->depositPercent;
        $suggestedDeposit = $inquiry->quoted_amount && $depositPercent > 0
            ? resolve(CateringDepositCalculator::class)->suggestedAmount($inquiry, $depositPercent)
            : null;

        $depositChip = match (true) {
            $depositPaid => ['label' => 'Deposit received', 'bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
            in_array($status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed], true) => ['label' => 'Deposit pending', 'bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
            default => null,
        };

        return [
            'inquiry' => $inquiry,
            'status' => $status,
            'order' => $inquiry->order,
            'eventDate' => $eventDate,
            'eventCountdown' => $eventCountdown,
            'eventPast' => $eventPast,
            'depositPaid' => $depositPaid,
            'depositPercent' => $depositPercent,
            'suggestedDeposit' => $suggestedDeposit,
            'depositChip' => $depositChip,
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
            ->action(function (array $data, CancelCateringInquiry $cancelInquiry): void {
                $reason = Arr::string($data, 'reason', '');
                $cancelInquiry($this->record, $reason !== '' ? $reason : null);

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
            ->action(function (array $data, UpdateCateringCustomerDetails $updateCustomer): void {
                $customer = new ValidatedInput($data);

                $updateCustomer(
                    $this->record,
                    $customer->string('customer_name')->toString(),
                    $customer->string('customer_email')->toString(),
                    $customer->filled('customer_phone')
                        ? $customer->string('customer_phone')->toString()
                        : null,
                );

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
            ->schema(CateringEventDetailsFields::make())
            ->action(function (array $data, UpdateCateringEventDetails $updateEvent): void {
                $event = new ValidatedInput($data);

                $updateEvent(
                    $this->record,
                    new CateringEventDetails(
                        eventType: $event->string('event_type')->toString(),
                        eventDate: $event->string('event_date')->toString(),
                        guestCount: $event->integer('guest_count'),
                        budget: $event->filled('budget') ? $event->float('budget') : null,
                        details: $event->string('details')->toString(),
                        dietaryRequirements: $event->filled('dietary_requirements')
                            ? $event->string('dietary_requirements')->toString()
                            : null,
                        venueAddress: $event->filled('venue_address')
                            ? $event->string('venue_address')->toString()
                            : null,
                    ),
                );

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
            ->action(function (array $data, CateringQuoteItemMapper $mapper, SyncCateringQuoteItems $syncItems): void {
                $rows = $mapper->map($data['items'] ?? []);
                $syncItems($this->record, $rows);

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
            ->action(function (SendCateringQuote $sendQuote): void {
                $sendQuote($this->record);

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
            ->action(function (ResendCateringQuote $resendQuote): void {
                $resendQuote($this->record);

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
            ->action(function (ConvertCateringInquiryToOrder $convertInquiry): void {
                try {
                    $order = $convertInquiry($this->record);
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
                    ->default(fn (CateringDepositCalculator $calculator, TenantSettings $settings): float => $calculator->suggestedAmount(
                        $this->record,
                        $settings->catering->depositPercent,
                    )),
                TextInput::make('reference')
                    ->label('Reference (check #, last-4, etc.)')
                    ->maxLength(255),
            ])
            ->action(function (array $data, RecordCateringDeposit $recordDeposit): void {
                $reference = Arr::string($data, 'reference', '');
                $recordDeposit(
                    $this->record,
                    Arr::float($data, 'amount'),
                    $reference !== '' ? $reference : null,
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
            ->action(function (array $data, UpdateCateringInquiryNotes $updateNotes): void {
                $notes = new ValidatedInput($data);

                $updateNotes(
                    $this->record,
                    $notes->filled('notes')
                        ? $notes->string('notes')->toString()
                        : null,
                );

                Notification::make()->title('Notes updated.')->success()->send();
            });
    }
}
