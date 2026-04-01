<?php

use App\Events\Platform\HealthCheckFailed;
use App\Listeners\Platform\SendHealthCheckAlertListener;
use App\Mail\Platform\HealthAlertMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends health check alert to the platform admin', function () {
    Mail::fake();
    config(['mail.platform_notify' => 'admin@kneadit.com']);

    $event = new HealthCheckFailed('Database connection timeout on tenant abc-bakery');

    $listener = new SendHealthCheckAlertListener;
    $listener->handle($event);

    Mail::assertQueued(HealthAlertMail::class, fn (HealthAlertMail $mail) => $mail->hasTo('admin@kneadit.com'));
});
