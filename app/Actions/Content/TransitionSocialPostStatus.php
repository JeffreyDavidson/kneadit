<?php

namespace App\Actions\Content;

use App\Enums\Marketing\SocialPostStatus;
use App\Models\Content\SocialPost;

class TransitionSocialPostStatus
{
    public function __invoke(SocialPost $post, SocialPostStatus $status): void
    {
        $post->update(['status' => $status]);
    }
}
