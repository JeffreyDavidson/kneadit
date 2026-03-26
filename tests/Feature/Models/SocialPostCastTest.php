<?php

use App\Enums\SocialPlatform;
use App\Models\SocialPost;

beforeEach(fn () => setUpTenantTest());

it('casts platform to SocialPlatform enum', function () {
    $post = SocialPost::query()->create([
        'platform' => 'instagram',
        'caption' => 'Test post',
        'status' => 'draft',
    ]);

    $post->refresh();

    expect($post->platform)->toBeInstanceOf(SocialPlatform::class)
        ->and($post->platform)->toBe(SocialPlatform::Instagram);
});
