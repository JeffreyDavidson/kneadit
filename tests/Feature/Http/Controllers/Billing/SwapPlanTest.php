<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('swap plan returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post('/billing/swap/nonexistent-plan')
        ->assertNotFound();
});
