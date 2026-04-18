<?php

use App\Mail\Platform\ContactFormMail;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\postJson;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['mail.from.address' => 'hello@getkneadit.app']);
});

test('marketing contact form queues an email to the platform address', function () {
    Mail::fake();

    $response = postJson(route('marketing.contact'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'I have a question about your pricing.',
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    Mail::assertQueued(ContactFormMail::class, function (ContactFormMail $mail) {
        return $mail->hasTo('hello@getkneadit.app')
            && $mail->senderName === 'Jane Doe'
            && $mail->senderEmail === 'jane@example.com'
            && str_contains($mail->body, 'pricing');
    });
});

test('marketing contact form rejects missing fields', function () {
    Mail::fake();

    $response = postJson(route('marketing.contact'), []);

    $response->assertUnprocessable();
    Mail::assertNothingQueued();
});

test('marketing contact form rejects an invalid email', function () {
    Mail::fake();

    $response = postJson(route('marketing.contact'), [
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'message' => 'Hello',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
    Mail::assertNothingQueued();
});
