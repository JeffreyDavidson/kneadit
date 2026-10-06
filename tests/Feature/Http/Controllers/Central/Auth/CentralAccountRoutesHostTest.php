<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    $owner = User::factory()->owner()->create();
    test()->tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $owner->id]);
    test()->tenant->createDomain(['domain' => 'sunrise']);
});

test('platform account pages answer 404 on a bakery host', function (string $path) {
    get("http://sunrise.kneadit.test/{$path}")->assertNotFound();
})->with([
    'register' => ['register'],
    'forgot password' => ['forgot-password'],
    'reset password' => ['reset-password/some-token'],
    'onboarding' => ['onboarding'],
]);

test('platform account forms refuse to run on a bakery host', function (string $path, array $data) {
    post("http://sunrise.kneadit.test/{$path}", $data)->assertNotFound();
})->with([
    'register' => ['register', [
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Someone Bakery',
        'terms' => '1',
    ]],
    'forgot password' => ['forgot-password', ['email' => 'someone@example.com']],
    'reset password' => ['reset-password', ['token' => 'x', 'email' => 'someone@example.com', 'password' => 'password123', 'password_confirmation' => 'password123']],
]);

test('email verification pages answer 404 on a bakery host', function () {
    actingAs(User::factory()->owner()->unverified()->create());

    get('http://sunrise.kneadit.test/email/verify')->assertNotFound();
    post('http://sunrise.kneadit.test/email/verification-notification')->assertNotFound();
});

test('the platform account pages still work on the central host', function () {
    get('http://kneadit.test/register')->assertOk();
    get('http://kneadit.test/forgot-password')->assertOk();
});
