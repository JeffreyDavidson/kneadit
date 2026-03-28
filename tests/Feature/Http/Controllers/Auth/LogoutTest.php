<?php

use App\Models\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('authenticated user can logout', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
