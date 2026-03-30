<?php

use App\Models\LoyaltyReward;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active rewards', function () {
    $active = LoyaltyReward::factory()->create(['is_active' => true]);
    LoyaltyReward::factory()->inactive()->create();

    $results = LoyaltyReward::query()->active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($active->id);
});
