<?php

use App\Models\Engagement\LoyaltyReward;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active rewards', function () {
    $active = LoyaltyReward::factory()->active()->create();
    LoyaltyReward::factory()->inactive()->create();

    $results = LoyaltyReward::query()->active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($active->id);
});
