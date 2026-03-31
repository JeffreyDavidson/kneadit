<?php

use App\Events\CampaignEmailQueued;
use App\Listeners\SendCampaignEmailListener;
use App\Mail\CustomerBlastMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends campaign email to the recipient', function () {
    Mail::fake();

    $event = new CampaignEmailQueued('subscriber@example.com', 'Spring Sale!', '<p>Big discounts this week.</p>');

    $listener = new SendCampaignEmailListener;
    $listener->handle($event);

    Mail::assertQueued(CustomerBlastMail::class, fn (CustomerBlastMail $mail) => $mail->hasTo('subscriber@example.com'));
});
