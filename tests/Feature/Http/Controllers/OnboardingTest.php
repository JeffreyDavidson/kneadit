<?php

use App\Models\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('onboarding page renders for authenticated user', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get('/onboarding')
        ->assertOk();
});
