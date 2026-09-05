<?php

use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Platform\TrialExpirationNotifier;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => setUpCentralTest());

test('sendReminder dispatches TrialReminding with the tenant store name', function () {
    Event::fake([TrialReminding::class]);
    $user = User::factory()->create(['email' => 'baker@example.com']);
    createTenant([
        'id' => 'happy-bakery',
        'email' => 'baker@example.com',
        'name' => 'Happy Bakery',
        'store_name' => 'Happy Bread Co',
    ]);
    $tenant = Tenant::query()->find('happy-bakery');

    $result = resolve(TrialExpirationNotifier::class)->sendReminder($user, $tenant, 7);

    expect($result)->toBeTrue();
    Event::assertDispatched(fn (TrialReminding $event): bool => $event->user->is($user)
        && $event->daysLeft === 7
        && $event->storeName === 'Happy Bread Co');
});

test('sendReminder falls back to tenant name when store_name is empty', function () {
    Event::fake([TrialReminding::class]);
    $user = User::factory()->create(['email' => 'baker@example.com']);
    createTenant([
        'id' => 'no-store-name',
        'email' => 'baker@example.com',
        'name' => 'Baker McBakerson',
        'store_name' => null,
    ]);
    $tenant = Tenant::query()->find('no-store-name');

    resolve(TrialExpirationNotifier::class)->sendReminder($user, $tenant, 3);

    Event::assertDispatched(fn (TrialReminding $event): bool => $event->storeName === 'Baker McBakerson');
});

test('notifyExpired dispatches TrialExpired with tenant id', function () {
    Event::fake([TrialExpired::class]);
    Config::set('app.url', 'http://kneadit.test:8000');
    $user = User::factory()->create();
    createTenant(['id' => 'expired-tenant']);
    $tenant = Tenant::query()->find('expired-tenant');

    resolve(TrialExpirationNotifier::class)->notifyExpired($user, $tenant);

    Event::assertDispatched(fn (TrialExpired $event): bool => $event->user->is($user)
        && $event->tenantId === 'expired-tenant'
        && $event->adminUrl === 'http://expired-tenant.kneadit.test:8000/admin');
});
