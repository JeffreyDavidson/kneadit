<?php

use App\Enums\SocialPlatform;
use App\Models\SocialPost;

beforeEach(fn () => setUpTenantTest());

it('casts platform to SocialPlatform enum', function () {
    $post = SocialPost::factory()->create([
        'platform' => SocialPlatform::Instagram,
    ]);

    $post->refresh();

    expect($post->platform)->toBeInstanceOf(SocialPlatform::class)
        ->and($post->platform)->toBe(SocialPlatform::Instagram);
});
