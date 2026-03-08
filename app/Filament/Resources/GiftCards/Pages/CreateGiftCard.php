<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Services\GiftCardService;
use Filament\Resources\Pages\CreateRecord;

class CreateGiftCard extends CreateRecord
{
    protected static string $resource = GiftCardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = new GiftCardService;
        $data['code'] = $service->generateCode();
        $data['current_balance'] = $data['initial_balance'];

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->transactions()->create([
            'amount' => $this->record->initial_balance,
            'type' => 'purchase',
            'notes' => 'Created by admin',
            'created_at' => now(),
        ]);
    }
}
