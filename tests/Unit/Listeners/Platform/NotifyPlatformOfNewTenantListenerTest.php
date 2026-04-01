<?php

use App\Events\Platform\TenantOnboarded;
use App\Listeners\Platform\NotifyPlatformOfNewTenantListener;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    test()->artisan('migrate:fresh');
    createCentralTables();
    Illuminate\Support\Facades\DB::purge('central');
    $pdo = Illuminate\Support\Facades\DB::connection('sqlite')->getPdo();
    Illuminate\Support\Facades\DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
});

test('it sends notification email to the platform admin', function () {
    Mail::fake();
    config(['mail.platform_notify' => 'admin@kneadit.com']);

    $user = User::factory()->create(['name' => 'Jane Baker', 'email' => 'jane@example.com']);
    createTenant(['id' => 'janes-bakery', 'store_name' => 'Jane\'s Bakery']);
    $tenant = Tenant::find('janes-bakery');

    $event = new TenantOnboarded($user, $tenant, 'https://janes-bakery.kneadit.test/admin');

    $listener = new NotifyPlatformOfNewTenantListener;
    $listener->handle($event);

    Mail::assertQueued(NewSubscriberNotificationMail::class, fn (NewSubscriberNotificationMail $mail) => $mail->hasTo('admin@kneadit.com'));
});
