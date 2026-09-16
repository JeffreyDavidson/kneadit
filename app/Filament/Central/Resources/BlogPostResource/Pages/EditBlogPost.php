<?php

namespace App\Filament\Central\Resources\BlogPostResource\Pages;

use App\Filament\Central\Resources\BlogPostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    #[\Override]
    protected static string $resource = BlogPostResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
