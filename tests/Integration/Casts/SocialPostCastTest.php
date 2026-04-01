<?php

use App\Enums\Marketing\SocialPlatform;
use App\Models\Content\SocialPost;

beforeEach(fn () => setUpTenantTest());

it('casts platform to SocialPlatform enum', function () {
    $post = SocialPost::factory()->create([
        'platform' => SocialPlatform::Instagram,
    ]);

    $post->refresh();

    expect($post->platform)->toBeInstanceOf(SocialPlatform::class)
        ->and($post->platform)->toBe(SocialPlatform::Instagram);
});
