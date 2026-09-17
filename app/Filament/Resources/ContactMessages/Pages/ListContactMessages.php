<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    #[\Override]
    protected static string $resource = ContactMessageResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
