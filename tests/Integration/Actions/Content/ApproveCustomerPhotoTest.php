<?php

use App\Actions\Content\ApproveCustomerPhoto;
use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it approves a customer photo', function () {
    $photo = CustomerPhoto::factory()->unapproved()->create();

    resolve(ApproveCustomerPhoto::class)($photo);

    expect($photo->fresh()->is_approved)->toBeTrue();
});
