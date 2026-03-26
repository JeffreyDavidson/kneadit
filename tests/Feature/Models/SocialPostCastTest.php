<?php

use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Models\SocialPost;

beforeEach(fn () => setUpTenantTest());

it('casts platform to SocialPlatform enum', function () {
    $post = SocialPost::query()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Test post',
        'status' => SocialPostStatus::Draft,
    ]);

    $post->refresh();

    expect($post->platform)->toBeInstanceOf(SocialPlatform::class)
        ->and($post->platform)->toBe(SocialPlatform::Instagram);
});
