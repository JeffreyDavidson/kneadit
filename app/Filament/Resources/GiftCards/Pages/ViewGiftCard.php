<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Actions\GiftCards\AddGiftCardCredit;
use App\Actions\GiftCards\ToggleGiftCardActive;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Models\Financial\GiftCard;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;

/**
 * @property-read GiftCard $record
 */
class ViewGiftCard extends ViewRecord
{
    #[\Override]
    protected static string $resource = GiftCardResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->color(fn (): string => $this->record->is_active ? 'danger' : 'success')
                ->authorize('update')
                ->requiresConfirmation()
                ->action(function (): void {
                    resolve(ToggleGiftCardActive::class)($this->record);
                }),

            Action::make('add_credit')
                ->label('Add Credit')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('success')
                ->authorize('update')
                ->schema([
                    MoneyInput::make('amount')
                        ->required()
                        ->minValue(0.01),
                    TextInput::make('notes')
                        ->placeholder('Reason for credit'),
                ])
                ->action(function (array $data): void {
                    resolve(AddGiftCardCredit::class)(
                        $this->record,
                        Arr::float($data, 'amount'),
                        Arr::string($data, 'notes', 'Credit added by admin'),
                    );
                }),

            EditAction::make(),
        ];
    }

    #[\Override]
    public function getSubNavigation(): array
    {
        return [];
    }
}
