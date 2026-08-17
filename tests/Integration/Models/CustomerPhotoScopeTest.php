<?php

use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('approved scope returns only approved photos', function () {
    $approved = CustomerPhoto::factory()->approved()->create();
    CustomerPhoto::factory()->create();

    $results = CustomerPhoto::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($approved->id);
});

test('withCaptionLike scope filters by caption', function () {
    CustomerPhoto::factory()->create(['caption' => 'Our catering event', 'is_approved' => true]);
    CustomerPhoto::factory()->create(['caption' => 'Birthday cake', 'is_approved' => true]);

    $results = CustomerPhoto::query()->approved()->withCaptionLike('catering')->get();

    expect($results)->toHaveCount(1);
});
