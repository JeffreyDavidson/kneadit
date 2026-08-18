<?php

use App\Events\Marketing\CampaignEmailQueued;
use App\Listeners\Marketing\SendCampaignEmailListener;
use App\Mail\Marketing\CustomerBlastMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends campaign email to the recipient', function () {
    Mail::fake();

    $event = new CampaignEmailQueued('subscriber@example.com', 'Spring Sale!', '<p>Big discounts this week.</p>');

    $listener = new SendCampaignEmailListener;
    $listener->handle($event);

    Mail::assertQueued(CustomerBlastMail::class, fn (CustomerBlastMail $mail) => $mail->hasTo('subscriber@example.com'));
});

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendCampaignEmailListener failed', Mockery::on(fn (array $context) => $context['email'] === 'subscriber@example.com'
            && $context['error'] === 'SMTP timeout'));

    $event = new CampaignEmailQueued('subscriber@example.com', 'Spring Sale!', '<p>Big discounts.</p>');

    $listener = new SendCampaignEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
