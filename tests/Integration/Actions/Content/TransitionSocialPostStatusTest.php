<?php

use App\Actions\Content\TransitionSocialPostStatus;
use App\Enums\Marketing\SocialPostStatus;
use App\Models\Content\SocialPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions social post from draft to scheduled', function () {
    $post = SocialPost::factory()->create(['status' => SocialPostStatus::Draft]);

    resolve(TransitionSocialPostStatus::class)($post, SocialPostStatus::Scheduled);

    expect($post->fresh()->status)->toBe(SocialPostStatus::Scheduled);
});
