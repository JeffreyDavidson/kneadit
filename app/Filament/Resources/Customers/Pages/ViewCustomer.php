<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Actions\Loyalty\AdjustLoyaltyPoints;
use App\Actions\Loyalty\RedeemLoyaltyPoints;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Presenters\CustomerPresenter;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Rule;

/**
 * Customer 360 — single-page aggregation of everything we know about
 * one customer: header stats, recent orders, notes, and address. Backed
 * by CustomerPresenter::toDetailArray() which already memoizes the
 * CustomerIntelligence metrics call.
 *
 * @property-read Customer $record
 */
class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.resources.customers.pages.view-customer';

    /** Inline note-add form state — mirrors the ViewTenant pattern. */
    #[Rule(['required', 'min:3'])]
    public string $noteBody = '';

    /**
     * The view template walks orders, customerNotes, and loyaltyPoints —
     * strict-mode preventLazyLoading 500s if any aren't loaded. Override
     * getRecord() so the relations are idempotently loaded on every
     * render, including post-Livewire-action re-renders where the model
     * is hydrated fresh without its prior relations.
     */
    public function getRecord(): Model
    {
        return parent::getRecord()->loadMissing([
            'orders',
            'customerNotes.createdBy',
            'loyaltyPoints',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('adjustPoints')
                ->label('Adjust Points')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->modalHeading('Adjust loyalty points')
                ->modalDescription('Use this for goodwill credits, corrections, or birthday gifts — anywhere you\'re adjusting at admin discretion. If the customer cashed in a reward, use Manual Redemption instead.')
                ->schema([
                    TextInput::make('points')
                        ->label('Points')
                        ->numeric()
                        ->required()
                        ->helperText('Positive to credit, negative to debit. e.g., 50 or -25.'),
                    TextInput::make('description')
                        ->label('Reason')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Customer service comp · birthday gift · correction'),
                ])
                ->action(function (array $data): void {
                    resolve(AdjustLoyaltyPoints::class)(
                        $this->record,
                        (int) $data['points'],
                        $data['description'],
                    );

                    $this->record->load('loyaltyPoints');

                    Notification::make()
                        ->title('Loyalty points adjusted')
                        ->body(($data['points'] >= 0 ? '+' : '') . $data['points'] . ' points · ' . $data['description'])
                        ->success()
                        ->send();
                }),

            Action::make('redeemPoints')
                ->label('Manual Redemption')
                ->icon(Heroicon::OutlinedGift)
                ->modalHeading('Manual point redemption')
                ->modalDescription('Use this when the customer redeemed a reward outside the normal flow — e.g., you honored a coupon at the counter. For admin-discretion comps or corrections, use Adjust Points instead.')
                ->schema([
                    TextInput::make('points')
                        ->label('Points to redeem')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->helperText('Points the customer cashed in for the reward.'),
                    TextInput::make('description')
                        ->label('What did the customer get?')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Free coffee · 10% off pickup'),
                ])
                ->action(function (array $data): void {
                    resolve(RedeemLoyaltyPoints::class)(
                        $this->record,
                        (int) $data['points'],
                        $data['description'],
                    );

                    $this->record->load('loyaltyPoints');

                    Notification::make()
                        ->title('Redemption recorded')
                        ->body($data['points'] . ' points redeemed · ' . $data['description'])
                        ->success()
                        ->send();
                }),
        ];
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        return [
            'detail' => CustomerPresenter::for($this->record)->toDetailArray(),
        ];
    }

    public function addNote(): void
    {
        $this->validate(['noteBody' => ['required', 'min:3']]);

        $this->record->customerNotes()->create([
            'note' => $this->noteBody,
            'created_by' => auth()->id(),
        ]);

        $this->noteBody = '';
        $this->record->load('customerNotes');

        Notification::make()
            ->title('Note added')
            ->success()
            ->send();
    }

    public function deleteNote(int $noteId): void
    {
        CustomerNote::query()
            ->where('customer_id', $this->record->id)
            ->where('id', $noteId)
            ->delete();

        $this->record->load('customerNotes');

        Notification::make()
            ->title('Note deleted')
            ->success()
            ->send();
    }
}
