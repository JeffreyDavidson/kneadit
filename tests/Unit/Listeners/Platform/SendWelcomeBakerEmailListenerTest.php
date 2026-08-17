<?php

use App\Events\Platform\TenantOnboarded;
use App\Listeners\Platform\SendWelcomeBakerEmailListener;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    test()->artisan('migrate:fresh');
    createCentralTables();
    Illuminate\Support\Facades\DB::purge('central');
    $pdo = Illuminate\Support\Facades\DB::connection('sqlite')->getPdo();
    Illuminate\Support\Facades\DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
});

test('it sends welcome email to the baker', function () {
    Mail::fake();

    $user = User::factory()->create(['name' => 'Jane Baker', 'email' => 'jane@example.com']);
    createTenant(['id' => 'janes-bakery', 'store_name' => 'Jane\'s Bakery']);
    $tenant = Tenant::query()->findOrFail('janes-bakery');

    $event = new TenantOnboarded($user, $tenant, 'https://janes-bakery.kneadit.test/admin');

    $listener = new SendWelcomeBakerEmailListener;
    $listener->handle($event);

    Mail::assertQueued(WelcomeBakerMail::class, fn (WelcomeBakerMail $mail) => $mail->hasTo('jane@example.com'));
});

test('failed method logs a warning with tenant id and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendWelcomeBakerEmailListener failed', Mockery::on(fn (array $context) => $context['tenant'] === 'janes-bakery'
            && $context['error'] === 'SMTP timeout'));

    $user = User::factory()->create();
    createTenant(['id' => 'janes-bakery', 'store_name' => 'Jane\'s Bakery']);
    $tenant = Tenant::query()->findOrFail('janes-bakery');

    $event = new TenantOnboarded($user, $tenant, 'https://janes-bakery.kneadit.test/admin');

    $listener = new SendWelcomeBakerEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
