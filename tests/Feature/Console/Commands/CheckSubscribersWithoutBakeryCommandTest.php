<?php

use App\Mail\Platform\SubscriberWithoutBakeryAlertMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
    Mail::fake();
});

function payingOwnerWithoutBakeryCheck(string $status = 'active'): User
{
    $owner = User::factory()->owner()->create();
    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => "sub_{$owner->id}",
        'stripe_status' => $status,
        'stripe_price' => 'price_starter_test',
    ]);

    return $owner;
}

test('alerts platform admins with the ids of paying accounts that have no bakery', function (string $status) {
    $admin = User::factory()->platformAdmin()->create(['email' => 'platform@example.com']);
    $owner = payingOwnerWithoutBakeryCheck($status);

    artisan('platform:check-subscribers-without-bakery')->assertSuccessful();

    Mail::assertQueued(SubscriberWithoutBakeryAlertMail::class, fn (SubscriberWithoutBakeryAlertMail $mail): bool => $mail->hasTo($admin->email)
        && $mail->userIds === [$owner->id]);
})->with(['active', 'trialing', 'past_due']);

test('the alert lists account ids only, no email addresses', function () {
    User::factory()->platformAdmin()->create();
    $owner = payingOwnerWithoutBakeryCheck();

    artisan('platform:check-subscribers-without-bakery')->assertSuccessful();

    Mail::assertQueued(SubscriberWithoutBakeryAlertMail::class, function (SubscriberWithoutBakeryAlertMail $mail) use ($owner): bool {
        $html = $mail->render();

        return str_contains($html, (string) $owner->id)
            && ! str_contains($html, $owner->email);
    });
});

test('does not alert when the paying account has a bakery', function () {
    User::factory()->platformAdmin()->create();
    $owner = payingOwnerWithoutBakeryCheck();
    Tenant::factory()->create(['user_id' => $owner->id]);

    artisan('platform:check-subscribers-without-bakery')
        ->expectsOutputToContain('Every paying account has a bakery')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not alert for an account whose subscription has ended or that never subscribed', function (?string $status) {
    User::factory()->platformAdmin()->create();
    $status === null ? User::factory()->owner()->create() : payingOwnerWithoutBakeryCheck($status);

    artisan('platform:check-subscribers-without-bakery')->assertSuccessful();

    Mail::assertNothingQueued();
})->with([null, 'canceled', 'incomplete_expired']);

test('sends nothing when there is no platform admin to alert', function () {
    payingOwnerWithoutBakeryCheck();

    artisan('platform:check-subscribers-without-bakery')
        ->expectsOutputToContain('No platform admins found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});
