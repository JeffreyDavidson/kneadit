<?php

use App\Events\Customers\CustomerPhotoSubmitted;
use App\Listeners\Customers\NotifyBakerOfCustomerPhotoListener;
use App\Mail\Customers\NewCustomerPhotoNotificationMail;
use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('queues notification to the configured store email', function () {
    settings(['store_email' => 'baker@example.com']);

    $photo = CustomerPhoto::factory()->create([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
    ]);

    (new NotifyBakerOfCustomerPhotoListener)->handle(new CustomerPhotoSubmitted($photo));

    Mail::assertQueued(
        NewCustomerPhotoNotificationMail::class,
        fn (NewCustomerPhotoNotificationMail $mail): bool => $mail->hasTo('baker@example.com')
            && $mail->photo->is($photo),
    );
});

test('skips when no store email is configured', function () {
    settings(['store_email' => '']);

    $photo = CustomerPhoto::factory()->create();

    (new NotifyBakerOfCustomerPhotoListener)->handle(new CustomerPhotoSubmitted($photo));

    Mail::assertNothingQueued();
});
