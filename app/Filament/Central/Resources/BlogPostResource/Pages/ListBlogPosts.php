<?php

namespace App\Filament\Central\Resources\BlogPostResource\Pages;

use App\Filament\Central\Resources\BlogPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
