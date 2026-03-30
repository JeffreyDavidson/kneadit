<?php

use App\Models\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('approved scope returns only approved photos', function () {
    $approved = CustomerPhoto::factory()->approved()->create();
    CustomerPhoto::factory()->create(['is_approved' => false]);

    $results = CustomerPhoto::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($approved->id);
});
