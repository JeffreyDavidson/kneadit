<?php

use App\Events\Platform\WeeklyDigestRequested;
use App\Listeners\Platform\SendWeeklyDigestEmailListener;
use App\Mail\Platform\WeeklyDigestMail;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends weekly digest email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new WeeklyDigestRequested(
        user: $user,
        stats: ['orders' => 12, 'revenue' => 450.00],
        topProducts: new Collection,
        atRiskCustomers: new Collection,
        upcomingCount: 3,
        storeName: 'Sweet Treats Bakery',
        adminUrl: 'https://sweet-treats.kneadit.test/admin',
    );

    $listener = new SendWeeklyDigestEmailListener;
    $listener->handle($event);

    Mail::assertQueued(WeeklyDigestMail::class, fn (WeeklyDigestMail $mail) => $mail->hasTo('baker@example.com'));
});

test('failed method logs a warning with user email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendWeeklyDigestEmailListener failed', Mockery::on(fn (array $context) => $context['user'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout'));

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new WeeklyDigestRequested(
        user: $user,
        stats: ['orders' => 12, 'revenue' => 450.00],
        topProducts: new Collection,
        atRiskCustomers: new Collection,
        upcomingCount: 3,
        storeName: 'Sweet Treats Bakery',
        adminUrl: 'https://sweet-treats.kneadit.test/admin',
    );

    $listener = new SendWeeklyDigestEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
