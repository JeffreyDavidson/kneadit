<?php

namespace App\Actions\Content;

use App\Enums\SocialPostStatus;
use App\Models\SocialPost;

class TransitionSocialPostStatus
{
    public function __invoke(SocialPost $post, SocialPostStatus $status): void
    {
        $post->update(['status' => $status]);
    }
}
