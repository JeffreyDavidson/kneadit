<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Actions\Loyalty\AdjustLoyaltyPoints;
use App\Actions\Loyalty\RedeemLoyaltyPoints;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customers\Customer;
use App\Presenters\CustomerPresenter;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('adjustPoints')
                ->label('Adjust Points')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->modalHeading('Adjust loyalty points')
                ->modalDescription('Manually credit or debit loyalty points. Use a positive number to add, negative to remove.')
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

                    Notification::make()
                        ->title('Loyalty points adjusted')
                        ->body(($data['points'] >= 0 ? '+' : '') . $data['points'] . ' points · ' . $data['description'])
                        ->success()
                        ->send();
                }),

            Action::make('redeemPoints')
                ->label('Manual Redemption')
                ->icon(Heroicon::OutlinedGift)
                ->color('warning')
                ->modalHeading('Manual point redemption')
                ->modalDescription('Record a redemption that happened outside the normal reward flow (e.g., honored a coupon at the counter).')
                ->schema([
                    TextInput::make('points')
                        ->label('Points to redeem')
                        ->numeric()
                        ->required()
                        ->minValue(1),
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
}
