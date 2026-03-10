<?php

namespace App\Filament\Central\Resources\MessageResource\Pages;

use App\Filament\Central\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('compose')
                ->label('Compose Message')
                ->icon('heroicon-o-pencil-square')
                ->url(MessageResource::getUrl('compose')),
        ];
    }
}
