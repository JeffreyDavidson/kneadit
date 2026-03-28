<?php

use App\Models\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('checkout returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post('/billing/checkout/nonexistent-plan')
        ->assertNotFound();
});
