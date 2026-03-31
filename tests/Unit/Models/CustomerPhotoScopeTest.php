<?php

use App\Models\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('withCaptionLike scope filters by caption', function () {
    CustomerPhoto::factory()->create(['caption' => 'Our catering event', 'is_approved' => true]);
    CustomerPhoto::factory()->create(['caption' => 'Birthday cake', 'is_approved' => true]);

    $results = CustomerPhoto::query()->approved()->withCaptionLike('catering')->get();

    expect($results)->toHaveCount(1);
});
