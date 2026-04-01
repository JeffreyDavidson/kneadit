<?php

use App\Events\Platform\WeeklyDigestRequested;
use App\Listeners\Platform\SendWeeklyDigestEmailListener;
use App\Mail\Platform\WeeklyDigestMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
