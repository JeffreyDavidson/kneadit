<?php

namespace App\Filament\Central\Resources\BlogPostResource\Pages;

use App\Filament\Central\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
}
