<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('checkout success redirects authenticated user to onboarding', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get('/billing/success')
        ->assertRedirect('/onboarding');
});
