<?php

use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('approved scope returns only approved photos', function () {
    $approved = CustomerPhoto::factory()->approved()->create();
    CustomerPhoto::factory()->create();

    $results = CustomerPhoto::query()->approved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($approved->id);
});
