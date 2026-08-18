<?php

use App\Mail\Platform\PaymentFailedAlertMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    DB::connection('central')->setPdo(DB::connection('sqlite')->getPdo());
});

test('envelope subject includes the user name and warning emoji', function () {
    $user = User::factory()->create(['name' => 'Ada Bakery']);

    $mail = new PaymentFailedAlertMail($user, null, 49.99);

    expect($mail->envelope()->subject)->toBe('⚠️ Payment Failed — Ada Bakery');
});

test('content uses the text-only blade template', function () {
    $user = User::factory()->create();

    $mail = new PaymentFailedAlertMail($user, null, 49.99);

    expect($mail->content()->text)->toBe('emails.platform.payment-failed-alert-text');
});

test('mailable accepts an optional Tenant', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();

    $mail = new PaymentFailedAlertMail($user, $tenant, 99.50);

    expect($mail->user)->toBeInstanceOf(User::class)
        ->and($mail->tenant)->toBeInstanceOf(Tenant::class)
        ->and($mail->amount)->toBe(99.50);
});
