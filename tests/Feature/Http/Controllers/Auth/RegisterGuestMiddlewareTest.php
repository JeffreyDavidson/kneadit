<?php

use App\Models\Staff\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('GET /register redirects when the user is already authenticated', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('register'));

    $response->assertRedirect();
    $response->assertDontSee('Start your bakery journey');
});

test('POST /register is rejected when the user is already authenticated', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('register'), [
        'name' => 'Already Authed',
        'email' => 'authed-' . uniqid() . '@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'bakery_name' => 'Already Authed Bakery',
        'terms' => true,
    ]);

    $response->assertRedirect();
    expect(User::query()->where('email', 'like', 'authed-%')->count())->toBe(0);
});
