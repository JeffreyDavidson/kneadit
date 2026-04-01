<?php

namespace App\Filament\Resources\GiftCardResource\Pages;

use App\Filament\Resources\GiftCardResource\GiftCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListGiftCards extends ListRecords
{
    protected static string $resource = GiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->mutateDataUsing(function (array $data): array {
                    $data['code'] = Str::upper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
                    $data['current_balance'] = $data['initial_balance'];

                    return $data;
                }),
        ];
    }
}
