<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use App\Services\Tenants\TenantUrlGenerator;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travel;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('verifies email with valid signed url', function () {
    $user = User::factory()->owner()->unverified()->create();

    actingAs($user)
        ->get(verificationUrlFor($user))
        ->assertRedirect(route('onboarding.show'));

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

/**
 * The link from the email the owner actually receives.
 */
function verificationUrlFor(User $user): string
{
    return (new OwnerVerifyEmailNotification)->toMail($user)->viewData['verificationUrl'];
}

test('an owner without a bakery goes to bakery setup after verifying and keeps the bakery name', function () {
    $user = User::factory()->owner()->unverified()->create();

    actingAs($user)
        ->withSession(['bakery_name' => 'Sunshine Bakery'])
        ->get(verificationUrlFor($user))
        ->assertRedirect(route('onboarding.show'))
        ->assertSessionHas('verified', true);

    actingAs($user->refresh())
        ->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('Sunshine Bakery');
});

test('an owner with a bakery goes to the bakery admin after verifying', function () {
    $user = User::factory()->owner()->unverified()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->get(verificationUrlFor($user))
        ->assertRedirect(app(TenantUrlGenerator::class)->admin($tenant))
        ->assertSessionHas('verified', true);
});

test('opening your own link twice still lands correctly', function () {
    $user = User::factory()->owner()->unverified()->create();
    $url = verificationUrlFor($user);

    actingAs($user)->get($url)->assertRedirect(route('onboarding.show'));
    actingAs($user->refresh())->get($url)->assertRedirect(route('onboarding.show'));
});

test('someone else\'s link is refused and verifies nothing', function () {
    $owner = User::factory()->owner()->unverified()->create();
    $other = User::factory()->owner()->unverified()->create();

    actingAs($other)
        ->get(verificationUrlFor($owner))
        ->assertForbidden();

    expect($owner->refresh()->hasVerifiedEmail())->toBeFalse()
        ->and($other->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('an expired link is refused', function () {
    $user = User::factory()->owner()->unverified()->create();
    $url = verificationUrlFor($user);

    travel(config('auth.verification.expire') + 1)->minutes();

    actingAs($user)
        ->get($url)
        ->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});
