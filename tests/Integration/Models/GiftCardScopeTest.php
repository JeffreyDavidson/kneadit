<?php

use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('usable scope returns active non-expired cards with balance', function () {
    $usable = GiftCard::factory()->create();
    GiftCard::factory()->depleted()->create();
    GiftCard::factory()->expired()->create();

    $results = GiftCard::query()->usable()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($usable->id);
});
