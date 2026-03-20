<?php

namespace App\Filament\Resources\SocialPosts\Pages;

use App\Filament\Resources\SocialPosts\SocialPostResource;
use Filament\Resources\Pages\EditRecord;

class EditSocialPost extends EditRecord
{
    use EditRecord\Concerns\HasSlideOverForm;

    protected static string $resource = SocialPostResource::class;
}
