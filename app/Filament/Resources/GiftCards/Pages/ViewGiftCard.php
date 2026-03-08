<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Services\GiftCardService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewGiftCard extends ViewRecord
{
    protected static string $resource = GiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn () => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['is_active' => ! $this->record->is_active]);
                }),

            Action::make('add_credit')
                ->label('Add Credit')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
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
                    $service = new GiftCardService;
                    $service->addCredit(
                        $this->record,
                        (float) $data['amount'],
                        $data['notes'] ?? 'Credit added by admin'
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
