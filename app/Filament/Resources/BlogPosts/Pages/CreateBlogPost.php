<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    #[\Override]
    protected static string $resource = BlogPostResource::class;
}
