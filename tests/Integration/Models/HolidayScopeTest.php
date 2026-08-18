<?php

use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('upcoming scope returns only future holidays', function () {
    $future = Holiday::factory()->create(['date' => now()->addMonth()]);
    Holiday::factory()->create(['date' => now()->subMonth()]);

    $results = Holiday::query()->upcoming()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($future->id);
});
