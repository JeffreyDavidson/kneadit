<?php

use App\Models\Staff\User;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('verifies email with valid signed url', function () {
    $user = User::factory()->owner()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/');

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});
