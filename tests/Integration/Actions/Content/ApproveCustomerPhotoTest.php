<?php

use App\Actions\Content\ApproveCustomerPhoto;
use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it approves a customer photo', function () {
    $photo = CustomerPhoto::factory()->create(['is_approved' => false]);

    resolve(ApproveCustomerPhoto::class)($photo);

    expect($photo->fresh()->is_approved)->toBeTrue();
});
