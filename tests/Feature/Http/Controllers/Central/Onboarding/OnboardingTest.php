<?php

use App\Models\Staff\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('onboarding page renders for authenticated user', function () {
    $user = User::factory()->owner()->create();

    actingAs($user)
        ->get(route('onboarding.show'))
        ->assertOk();
});
