<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Actions\GiftCards\AddGiftCardCredit;
use App\Actions\GiftCards\ToggleGiftCardActive;
use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Models\Financial\GiftCard;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read GiftCard $record
 */
class ViewGiftCard extends ViewRecord
{
    protected static string $resource = GiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn () => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->authorize('update')
                ->requiresConfirmation()
                ->action(function () {
                    resolve(ToggleGiftCardActive::class)($this->record);
                }),

            Action::make('add_credit')
                ->label('Add Credit')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('success')
                ->authorize('update')
                ->schema([
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->prefix('$'),
                    TextInput::make('notes')
                        ->placeholder('Reason for credit'),
                ])
                ->action(function (array $data) {
                    resolve(AddGiftCardCredit::class)(
                        $this->record,
                        (float) $data['amount'],
                        $data['notes'] ?? 'Credit added by admin',
                    );
                }),

            EditAction::make(),
        ];
    }

    public function getSubNavigation(): array
    {
        return [];
    }
}
